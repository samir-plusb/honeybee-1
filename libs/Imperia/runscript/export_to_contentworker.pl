#!/usr/bin/perl
#
#
# Trigger import of Imperia news pages in ContentWorker
#
# Script expects a list of Imperia URI in command line
#
# Paramters in system.conf
# <ul>
# <li>ContentWorker.ImportUrl - URL of ContentWorker import interface
# </ul>
#
# @version $Id$
#

utf8;

BEGIN {
    unshift @INC, $0 =~ /(.*)[\\\/]/ ?
        "$1/../../modules/core"
        : '../../modules/core';
    require Imperia::LowLevel::Bootstrap;
}

use strict;
use JSON;
use Data::Dumper;
use LWP::UserAgent;
use URI::Escape;
use Imperia::LowLevel::SystemConf qw(%SYSTEM_CONF);
use PageParser qw(get_saved_meta);
use Imperia::LowLevel::IOError qw(io_error);

my $importUrl = $SYSTEM_CONF{'ContentWorker.ImportUrl'};
die "Please define: SYSTEM_CONF{'ContentWorker.ImportUrl'}" unless $importUrl =~ /^https?:\/\//;

my $xmlDumpUrl = $SYSTEM_CONF{'ContentWorker.XmlDumpUrl'};
die "Please define: SYSTEM_CONF{'ContentWorker.XmlDumpUrl'}" unless $xmlDumpUrl =~ /^https?:\/\//;

my $basepath = $SYSTEM_CONF{'DOCUMENT-ROOT'};
my @docs = ();

foreach my $uri (@ARGV)
{
    # process only messages for defined url subtrees
    # @todo define regular expression in system.conf
    next unless $uri =~ /^\/(ba-|polizei)/;

    my $metainfo = PageParser::get_saved_meta("$basepath/$uri");
    my $template = $metainfo->getValues('template');

    # process only messages using defined templates
    # @todo define regular expression in system.conf
    next unless $template =~ /(land_polizei_presse_fahndung|land_pressemeldung)/;

    my $data = {
        'uri' => $uri,
        '__imperia_node_id' => scalar $metainfo->getValues('__imperia_node_id'),
        '__imperia_modified' => scalar $metainfo->getValues('__imperia_modified'),
        'publish_date' =>  scalar $metainfo->getValues('publish_date'),
        'expiry_date' =>  scalar $metainfo->getValues('expiry_date'),
    };

    push @docs, $data;
}

my $json = JSON->new->allow_nonref;
my $json_text = $json->encode( \@docs );

my $ua = LWP::UserAgent->new;
$ua->agent('$Id$');

foreach my $url (split /\s+/, $importUrl)
{
	my $req = HTTP::Request->new(POST => $url);
	$req->content_type('application/x-www-form-urlencoded');
    $req->header('Accept' => 'application/json');
	$req->content('data='.uri_escape($json_text));
	my $res = $ua->request($req);
	warn "$url: ".$res->status_line unless $res->is_success;
}
