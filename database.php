<?php

// This file adds a little bit of a wrapper over MySQL, and a little
//  bit of convenience functionality.

require_once 'common.php';

$dblink = NULL;

function get_dblink()
{
    global $dblink;

    if ($dblink == NULL)
    {
        $dblink = mysql_connect('localhost', 'alextreg', 'kjskdjasd923asd');
        if (!$dblink)
        {
            $err = mysql_error();
            write_error("Failed to open database link: ${err}.");
            $dblink = NULL;
        } // if

        if (!mysql_select_db("alextreg"))
        {
            $err = mysql_error();
            write_error("Failed to select database: ${err}.");
            mysql_close($dblink);
            $dblink = NULL;
        } // if
    } // if

    return($dblink);
} // get_dblink


function db_escape_string($str)
{
    return(mysql_escape_string($str));
} // db_escape_string


function do_dbquery($sql, $link = NULL)
{
    if ($link == NULL)
        $link = get_dblink();

    if ($link == NULL)
        return(false);

    $rc = mysql_query($sql, $link);
    if ($rc == false)
    {
        $err = mysql_error();
        write_error("Problem in SELECT statement: {$err}");
        return(false);
    } // if

    return($rc);
} // do_dbquery


function db_num_rows($query)
{
    return(mysql_num_rows($query));
} // db_num_rows


function db_fetch_array($query)
{
    return(mysql_fetch_array($query));
} // db_fetch_array


function db_free_result($query)
{
    return(mysql_free_result($query));
} // db_free_result

?>