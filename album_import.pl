#!/usr/bin/perl -w

use strict;

package Bubba::Album;
use DBI;
use Image::ExifTool;
use File::Basename;

use Perl6::Say;
use JSON;
use threads;
use threads::shared;
use Thread::Queue;
use base qw(Net::Daemon);


use vars qw($exit);
use vars qw($VERSION);

$VERSION = '0.0.1';

use constant SOCKNAME		=> "/tmp/bubba-album.socket";
use constant PIDFILE		=> '/tmp/bubba-album.pid';
use constant THUMB_WIDTH	=> 80;
use constant THUMB_HEIGHT	=> 60;
use constant SCALE_WIDTH	=> 600;
use constant CACHE_PATH		=> '/var/lib/album/thumbs';
use constant THUMB_PATH		=> CACHE_PATH . '/thumbs';
use constant SCALE_PATH		=> CACHE_PATH . '/rescaled';

sub new($$;$) {
	my($class, $attr, $args) = @_;
	my($self) = $class->SUPER::new($attr, $args);
	$self->{EXIF_WORK_QUEUE} = new Thread::Queue;
	$self->{THUMB_WORK_QUEUE} = new Thread::Queue;
	$self->{IS_RUNNING} = 0;
	$self->{IS_IDLE} = 0;

	$self;
}

sub PostDaemonize {
	my ($self) = @_;
	share($self->{IS_RUNNING});
	share($self->{IS_IDLE});
	threads->create( \&_process_exif_work_queue, $self );
	threads->create( \&_process_thumb_work_queue, $self );
}


sub Loop($) {
	my ($self) = @_;
	$self->{IS_RUNNING} = $self->{EXIF_WORK_QUEUE}->pending || $self->{THUMB_WORK_QUEUE}->pending;
	if( $self->{IS_RUNNING} ) {
		$self->Debug("Loop: is still running");
		$self->{IS_IDLE} = 0;
		return;
	}
	$self->Debug("Loop: We are not running at the moment, idle for $self->{IS_IDLE} revolutions");
	if( ++$self->{IS_IDLE} >= 2 ) {
		$self->Log('notice', "Timeout: %s server terminating", ref($self));
		# cleaning up
		-f $self->{'pidfile'} and unlink $self->{'pidfile'};
		-S $self->{'localpath'} and unlink $self->{'localpath'};
		kill 'INT', $$;
	}


}

sub Run($) {
	my $json = new JSON;
	my ($self) = @_;
	$self->Debug('in Run');
	my ($line,$sock);
	$sock = $self->{'socket'};
	while(1) {
		if (!defined($line = $sock->getline())) {
			if ($sock->error()) {
				$self->Error("Client connection error %s",
					$sock->error());
			}
			$sock->close();
			return;
		}
		$line =~ s/\s+$//; # Remove CRLF

		my $request;
		eval { $request = $json->decode($line) } || $self->Fatal("Unable to parse \"%s\": %s", $line, $!);
		if( exists $request->{action} ) {
			my $cmd = $request->{action};
			if( $cmd eq 'add' ) {
				$self->{IS_RUNNING} = 1;
				my %current : shared = (
					'file' => $request->{file},
					'id' => $request->{id},
				);
				$self->{EXIF_WORK_QUEUE}->enqueue(\%current);
				$self->Log('notice', "Added $request->{file} with id $request->{id}");
				$sock->say( $json->encode( { 'response' => 'added' } ) );
			}
		}
	}
}

sub _process_exif_work_queue {
	my ($self) = @_;
	threads->detach();
	my $dbh;
	{
		my $db = { do '/etc/album/debian-db.perl' };
		$dbh = DBI->connect( "dbi\:$db->{type}\:database=$db->{name};host=$db->{host};port=$db->{port}", $db->{user}, $db->{pass}, { RaiseError => 1, AutoCommit => 1} );
		$dbh->do("SET NAMES UTF8");
	}
	my $sth = $dbh->prepare("UPDATE image SET name=?, caption=? WHERE id=?");

	while(1) {
		next unless $self->{EXIF_WORK_QUEUE}->pending;

		my $current = $self->{EXIF_WORK_QUEUE}->dequeue;

		my $exifTool = new Image::ExifTool();

		$self->Debug("Processing EXIF for $current->{file}");

		$exifTool->ExtractInfo( $current->{file} );

		my $info = $exifTool->GetInfo(
			'ImageWidth',
			'ImageHeight',
			'Title',
			'Subject',
		);

		my $title = $info->{Title} ? $info->{Title} : basename( $current->{file} ); 

		$sth->execute( $title, $info->{Subject}, $current->{id});

		$current->{width} = $info->{ImageWidth};
		$current->{height} = $info->{ImageHeight};

		$self->{THUMB_WORK_QUEUE}->enqueue($current);

	}
}

sub _process_thumb_work_queue {
	threads->detach();
	my ($self) = @_;
	while(1) {
		next if $self->{EXIF_WORK_QUEUE}->pending;
		next unless $self->{THUMB_WORK_QUEUE}->pending;

		my $current = $self->{THUMB_WORK_QUEUE}->dequeue;

		$self->Debug("Processing thumbs for $current->{file}");

		system( 
			"epeg",
			"-w ".THUMB_WIDTH,
			"-h ".THUMB_HEIGHT,
			$current->{file},
			THUMB_PATH . "/$current->{id}"
		);

		system( 
			"epeg",
			"-w ".SCALE_WIDTH,
			"-h ".SCALE_WIDTH,
			"-m ".SCALE_WIDTH,
			$current->{file},
			SCALE_PATH . "/$current->{id}"
		);
	}
}

package main;

unless( -d Bubba::Album::CACHE_PATH ) {
	mkdir Bubba::Album::CACHE_PATH;
}
unless( -d Bubba::Album::THUMB_PATH ) {
	mkdir Bubba::Album::THUMB_PATH;
}
unless( -d Bubba::Album::SCALE_PATH ) {
	mkdir Bubba::Album::SCALE_PATH;
}

my $server = new Bubba::Album({
		localpath => Bubba::Album::SOCKNAME, 
		pidfile => Bubba::Album::PIDFILE,
		'loop-timeout' => 30, 
	}, \@ARGV);
$server->Bind();
