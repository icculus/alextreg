#!/usr/bin/perl -w

use strict;
use warnings;

if (not defined $ENV{'REMOTE_USER'}) {
    print "Content-type: text/plain\n\n";
    print "You are misconfigured. There needs to be basic auth in this dir.\n";
    exit 1;
}

exec '../../wiki/wiki.pl';
print "Content-type: text/plain\n\n";
print "exec failed: $!\n";
exit 1;

# end of vendor wiki.pl ...

