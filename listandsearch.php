<?php

require_once 'operations.php';
require_once 'database.php';
require_once 'common.php';

$queryfuncs = array();


$queryfuncs['extension'] = 'find_extension';
function find_extension($wantname)
{
    $sql = 'select extname, id from alextreg_extensions';

    if ($wantname)
    {
        $sqlwantname = db_escape_string($wantname);
        $sql += " where extname=$sqlwantname";
    } // if

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...
    else
    {
        if (($wantname) and (db_num_rows($query) != 1))
            write_error('(Unexpected number of results from database!)');

        print("<ul>\n");
        while ( ($row = db_fetch_array($query)) != false )
        {
            $url = get_alext_wiki_url($row['id']);
            print("  <li><a href='$url'>${row['extname']}</a>\n");
        } // while
        print("</ul>\n\n");
    } // else

    db_free_result($query);
} // find_extension


function find_token($additionalsql, $wantname)
{
    $sql = 'select tok.tokenname as tokenname,' +
           ' tok.tokenval as tokenval,' +
           ' ext.id as extid' +
           ' from alextreg_tokens as tok' +
           ' left outer join alextreg_extensions as ext' +
           ' on tok.extid=ext.id' +
           $additionalsql;

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...
    else
    {
        if (($wantname) and (db_num_rows($query) != 1))
            write_error('(Unexpected number of results from database!)');

        print("<ul>\n");
        while ( ($row = db_fetch_array($query)) != false )
        {
            $url = get_alext_wiki_url($row['extid']);
            $hex = sprintf("0x%X", $row['tokenval']);  // !!! FIXME: faster way to do this?
            print("  <li>${row['tokenname']} ($hex)");
            print(" from <a href='$url'>${row['extname']}</a>\n");
        } // while
        print("</ul>\n\n");
    } // else

    db_free_result($query);
} // find_token


$queryfuncs['tokenname'] = 'find_tokenname';
function find_tokenname($wantname)
{
    $additionalsql = '';
    if ($wantname)
    {
        $sqlwantname = db_escape_string($wantname);
        $additionalsql += " where tok.tokenname=$sqlwantname";
    } // if

    find_token($additionalsql, $wantname);
} // find_tokenname


$queryfuncs['tokenval'] = 'find_tokenvalue';
function find_tokenvalue($wantname)
{
    $additionalsql = '';
    if ($wantname)
    {
        $sqlwantname = db_escape_string($wantname);
        $additionalsql += " where tok.tokenval=$sqlwantname";
    } // if

    find_token($additionalsql, $wantname);
} // find_tokenname


$queryfuncs['entrypoint'] = 'find_entrypoint';
function find_entrypoint($wantname)
{
    write_error('Not implemented.');  // !!! FIXME
} // find_entrypoint


$queryfuncs['anything'] = 'find_anything';
function find_anything($wantname)
{
    write_error('Not implemented.');  // !!! FIXME
} // find_anything


$operations['op_findone'] = 'op_findone';
function op_findone()
{
    global $queryfuncs;

    $wanttype = $_REQUEST['wanttype'];
    $wantname = $_REQUEST['wantname'];
    write_debug("called op_findone($wanttype, $wantname)");

    if ( (empty($wantname)) or (empty($wanttype)) )
    {
        write_error('Please fill out all fields.');
        return;
    } // if

    $queryfunc = $queryfuncs[$wanttype];
    if (!isset($queryfunc))
    {
        write_error('Invalid search type.');
        return;
    } // if

    $queryfunc($wantname);
} // op_findone


$operations['op_findall'] = 'op_findall';
function op_findall()
{
    $wanttype = $_REQUEST['wanttype'];
    write_debug("called op_findall($wanttype)");
    write_error('Not implemented.');  // !!! FIXME
} // op_findall



function render_search_ui()
{
    print <<<EOF

<p>
Where do you want to go today?

<p>
<form method="post" action="${_SERVER['PHP_SELF']}">
  I want
  <select name="wanttype" size="1">
    <option selected value="extension">an extension</option>
    <option value="tokenname">a token name</option>
    <option value="tokenvalue">a token value</option>
    <option value="entrypoint">an entry point</option>
    <option value="anything">anything</option>
  </select>
  named <input type="text" name="wantname" value="">.
  <input type="hidden" name="operation" value="op_findone">
  <input type="submit" name="form_submit" value="Go!">
  <input type="reset" value="Clear">
</form>

<p>
...or...

<p>
<form method="post" action="${_SERVER['PHP_SELF']}">
  I want a list of all known
  <select name="wanttype" size="1">
    <option selected value="extensions">extensions</option>
    <option value="tokennames">tokens</option>
    <option value="entrypoints">entry points</option>
  </select>.
  <input type="hidden" name="operation" value="op_findall">
  <input type="submit" name="form_submit" value="Go!">
</form>

EOF;

} // render_search_ui

?>