<?php

$disable_debug = false;

function write_error($err)
{
    echo "<p><center><font color='#FF0000'>";
    echo   "ERROR: $err<br>";
    echo "</font></center>\n";
} // write_error

function write_debug($dbg)
{
    global $disable_debug;
    if ($disable_debug)
        return;

    echo "<p><center><font color='#0000FF'>";
    echo   "DEBUG: $dbg<br>";
    echo "</font></center>\n";
} // write_debug

function get_alext_wiki_url($extname)
{
    return("wiki/$extname");  // !!! FIXME
} // get_alext_wiki_url

?>