<?php

$enable_debug = (isset($_REQUEST['debug']));

$extflags_public = (1 << 0);

function is_authorized_vendor()
{
    return(!empty($_SERVER['REMOTE_USER']));
} // is_authorized_vendor

function write_error($err)
{
    echo "<p><center><font color='#FF0000'>";
    echo   "ERROR: $err<br>";
    echo "</font></center>\n";
} // write_error

function write_debug($dbg)
{
    global $enable_debug;
    if ($enable_debug)
    {
        echo "<p><center><font color='#0000FF'>";
        echo   "DEBUG: $dbg<br>";
        echo "</font></center>\n";
    } // if
} // write_debug


function current_sql_datetime()
{
    $t = localtime(time(), true);
    return( "" . ($t['tm_year'] + 1900) . '-' .
                 ($t['tm_mon'] + 1) . '-' .
                 ($t['tm_mday']) . ' ' .
                 ($t['tm_hour']) . ':' .
                 ($t['tm_min']) . ':' .
                 ($t['tm_sec']) );
} // current_sql_datetime

function get_alext_wiki_url($extname)
{
    $htmlextname = htmlentities($extname, ENT_QUOTES);
    return("wiki/wiki.pl?$htmlextname");
} // get_alext_wiki_url

function get_alext_url($extname)
{
    $htmlextname = htmlentities($extname, ENT_QUOTES);
    return("${_SERVER['PHP_SELF']}?operation=op_showext&extname=${htmlextname}");
} // get_alext_url

?>