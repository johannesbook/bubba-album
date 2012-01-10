#!/usr/bin/perl -w

use strict;

use DBI;
use Image::ExifTool;
use Image::Magick;
use File::Basename;
use File::MimeInfo;
use List::Util qw(max);
use Proc::Daemon;
use Sys::Syslog qw(:standard :macros);

use constant PIDFILE		=> '/tmp/bubba-album.pid';
use constant THUMB_WIDTH	=> 100;
use constant THUMB_HEIGHT	=> 100;
use constant SCALE_WIDTH	=> 600;
use constant CACHE_PATH		=> '/var/lib/album/thumbs';
use constant THUMB_PATH		=> CACHE_PATH . '/thumbs';
use constant SCALE_PATH		=> CACHE_PATH . '/rescaled';
use constant SPOOL_PATH     => '/var/spool/album';


unless( -d CACHE_PATH ) {
    mkdir CACHE_PATH;
}
unless( -d THUMB_PATH ) {
    mkdir THUMB_PATH;
}
unless( -d SCALE_PATH ) {
    mkdir SCALE_PATH;
}
unless( -d SPOOL_PATH ) {
    mkdir SPOOL_PATH;
}

my $daemon = Proc::Daemon->new(
    pid_file => PIDFILE,
    work_dir => '/'
);

my $kid_pid = $daemon->Init;

if( $kid_pid ) {
    exit;
}

openlog("album-import", "", LOG_USER);
syslog(LOG_INFO, "Starting album import worker");

my $dbh;
{
    my $db = { do '/etc/album/debian-db.perl' };
    $dbh = DBI->connect(
        "dbi\:$db->{type}\:database=$db->{name};host=$db->{host};port=$db->{port}",
        $db->{user},
        $db->{pass},
        {
            RaiseError => 1,
            AutoCommit => 1
        }
    );
    $dbh->do("SET NAMES UTF8");
}
my $update_image_table = $dbh->prepare("UPDATE image SET name=?, caption=? WHERE id=?");


LOOP: while(1) {
    if( opendir( my $spool, SPOOL_PATH ) ) {
        # Grab all current symlinks in the spool dir
        my %queue = map { $_ => readlink(SPOOL_PATH . '/' . $_) } grep { -l SPOOL_PATH . '/' . $_ } readdir( $spool );
        unless(scalar keys %queue) {
            syslog(LOG_INFO, "processing completed, shutting down");
            # we are done this time
            last LOOP;
        }
        while(my($id, $image) = each(%queue) ) {
            if($image) {
                syslog(LOG_INFO, "Processing image %s with id %d", $image, $id);
                process_exif($id, $image);
                process_thumb($id, $image);
            }
            unlink(SPOOL_PATH . '/' . $id);
        }
        # Don't be too hasty here.
        closedir( $spool );
        sleep 10;
    }
}

sub process_exif {
    my( $id, $image ) = @_;

    my $exifTool = new Image::ExifTool();

    $exifTool->ExtractInfo( $image );

    my $info = $exifTool->GetInfo(
        'ImageWidth',
        'ImageHeight',
        'Title',
        'Subject',
    );

    my $title = $info->{Title} ? $info->{Title} : basename( $image );

    $update_image_table->execute( $title, $info->{Subject}, $id);
}

sub process_thumb {
    my( $id, $image ) = @_;

    my $mimetype = mimetype($image);

    if( $mimetype eq "image/png" ) {
        my $p = new Image::Magick;
        my $x;
        $x=$p->Read($image);
        $x=$p->Thumbnail( geometry => SCALE_WIDTH."x" );
        $x=$p->Write(SCALE_PATH . "/$id");
        $x=$p->Set( Gravity => 'Center' );
        $x=$p->Thumbnail( geometry => THUMB_WIDTH.'x'.THUMB_HEIGHT.'^' );
        $x=$p->Set(background => 'transparent');
        $x=$p->Extent( geometry => THUMB_WIDTH.'x'.THUMB_HEIGHT );
        $x=$p->Write(THUMB_PATH . "/$id");
    } elsif( $mimetype eq "image/jpg" || $mimetype eq "image/jpeg" ) {
        system(
            "epeg",
            "-m",
            max( THUMB_HEIGHT, THUMB_WIDTH ) * 2,
            $image,
            THUMB_PATH . "/$id"
        );

        system(
            "epeg",
            "-m ".SCALE_WIDTH,
            $image,
            SCALE_PATH . "/$id"
        );
    }
}

