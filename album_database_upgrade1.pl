#!/usr/bin/perl 
#===============================================================================
#
#         FILE:  album_database_upgrade1.pl
#
#        USAGE:  ./album_database_upgrade1.pl 
#
#  DESCRIPTION:  
#
#      OPTIONS:  ---
# REQUIREMENTS:  ---
#         BUGS:  ---
#        NOTES:  ---
#       AUTHOR:   (), <>
#      COMPANY:  
#      VERSION:  1.0
#      CREATED:  24/05/10 13:46:32 CEST
#     REVISION:  ---
#===============================================================================

use strict;
use warnings;
use DBI;

my $db = { do '/etc/album/debian-db.perl' };
my $dbh = DBI->connect( "dbi\:$db->{type}\:database=$db->{name};host=$db->{host};port=$db->{port}", $db->{user}, $db->{pass} );

use Data::Dumper;
my $users = $dbh->selectall_arrayref($dbh->prepare("SELECT id, username, password FROM users"), { Slice => {}});
my $accesses = $dbh->selectall_arrayref($dbh->prepare("SELECT user, album FROM access"), { Slice => {}});
use XML::LibXML;
my $parser = new XML::LibXML();

my $auth_file = '/etc/bubba_auth.xml';
my $doc;
if( -f $auth_file ) {
	$doc = $parser->parse_file($auth_file);
} else {
	$doc = $parser->parse_string("<auth/>");
}

my $auth_node = $doc->documentElement();

my @removed_users;
my %user_map;
foreach my $user( @$users ) {
	system("getent", "passwd", $user->{'username'});
	if( $?>>8==0 || $auth_node->find("boolean(user[\@username='$user->{username}'])") ){
		next;
	}
	$user_map{$user->{'id'}}=$user->{'username'};

	my $current = $auth_node->appendChild($doc->createElement('user'));
	$current->setAttribute('username', $user->{'username'});
	$current->setAttribute('realname', $user->{'username'});
	$current->setAttribute('password', $user->{'password'});
	$current->appendTextChild( 'group', 'album' );
}
my @repopulate_access;

foreach my $access( @$accesses ) {
	if( defined $user_map{$access->{'user'}} ) {
		my $qusername = $dbh->quote($user_map{$access->{'user'}});
		my $qalbum= $dbh->quote($access->{'album'});
		push @repopulate_access, "INSERT INTO access (username, album) VALUES ( $qusername, $qalbum )";
	}
}

print $doc->toFile( $auth_file, 0);

$dbh->do("TRUNCATE TABLE access");
$dbh->do("ALTER TABLE access DROP PRIMARY KEY");
$dbh->do("ALTER TABLE access DROP COLUMN user");
$dbh->do("ALTER TABLE access ADD COLUMN username varchar(255) NOT NULL");
$dbh->do("ALTER TABLE album ADD COLUMN modified timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
$dbh->do("ALTER TABLE album ADD COLUMN created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'");
$dbh->do("ALTER TABLE access ADD PRIMARY KEY (username, album)");
$dbh->do("ALTER TABLE access ENGINE=InnoDB DEFAULT CHARACTER SET utf8");
$dbh->do("ALTER TABLE sessions ENGINE=InnoDB DEFAULT CHARACTER SET utf8");
$dbh->do("DROP TABLE IF EXISTS users");

foreach my $query( @repopulate_access ) {
	$dbh->do($query);
}
