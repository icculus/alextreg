<?php

require_once 'operations.php';
require_once 'database.php';
require_once 'common.php';

$queryfuncs = array();


function render_extension_list($wantname, $query)
{
    $count = db_num_rows($query);
    if (($wantname) and ($count > 1))
        write_error('(Unexpected number of results from database!)');

    print("<ul>\n");
    while ( ($row = db_fetch_array($query)) != false )
    {
        $url = get_alext_url($row['extname']);
        print("  <li><a href='$url'>${row['extname']}</a>\n");
    } // while
    print("</ul>\n<p>Total results: $count\n");
} // render_extension_list

function render_token_list($wantname, $query)
{
    $count = db_num_rows($query);
    if (($wantname) and ($count > 1))
        write_error('(Unexpected number of results from database!)');

    print("<ul>\n");
    while ( ($row = db_fetch_array($query)) != false )
    {
        $url = get_alext_url($row['extname']);
        $hex = sprintf("0x%X", $row['tokenval']);  // !!! FIXME: faster way to do this?
        print("  <li>${row['tokenname']} ($hex)");
        print(" from <a href='$url'>${row['extname']}</a>\n");
    } // while
    print("</ul>\n<p>Total results: $count\n");
} // render_token_list


function render_entrypoint_list($wantname, $query)
{
    $count = db_num_rows($query);
    if (($wantname) and ($count > 1))
        write_error('(Unexpected number of results from database!)');

    print("<ul>\n");
    while ( ($row = db_fetch_array($query)) != false )
    {
        $url = get_alext_url($row['extname']);
        print("  <li>${row['entrypointname']} ");
        print(" from <a href='$url'>${row['extname']}</a>\n");
    } // while
    print("</ul>\n<p>Total results: $count\n");
} // render_entrypoint_list


$queryfuncs['extension'] = 'find_extension';
function find_extension($wantname)
{
    global $extflags_public;

    $sql = 'select extname from alextreg_extensions' .
           ' where (1=1)';

    if (!is_authorized_vendor())
        $sql .= " and (flags & $extflags_public)";

    if ($wantname)
    {
        $sqlwantname = db_escape_string($wantname);
        $sql .= " and (extname='$sqlwantname')";
    } // if

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...
    else
        render_extension_list($wantname, $query);

    db_free_result($query);
} // find_extension


function find_token($additionalsql, $wantname)
{
    global $extflags_public;

    $sql = 'select tok.tokenname as tokenname,' .
           ' tok.tokenval as tokenval,' .
           ' ext.extname as extname' .
           ' from alextreg_tokens as tok' .
           ' left outer join alextreg_extensions as ext' .
           ' on tok.extid=ext.id where (1=1)' .
           $additionalsql;

    if (!is_authorized_vendor())
        $sql .= " and (ext.flags & $extflags_public)";

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...
    else
        render_token_list($wantname, $query);

    db_free_result($query);
} // find_token


$queryfuncs['tokenname'] = 'find_tokenname';
function find_tokenname($wantname)
{
    $additionalsql = '';
    if ($wantname)
    {
        $sqlwantname = db_escape_string($wantname);
        $additionalsql .= " and (tok.tokenname='$sqlwantname')";
    } // if

    find_token($additionalsql, $wantname);
} // find_tokenname


$queryfuncs['tokenvalue'] = 'find_tokenvalue';
function find_tokenvalue($wantname)
{
    $additionalsql = '';
    if ($wantname)
    {
        if (!is_numeric($wantname))
            return;
        $sqlwantname = db_escape_string($wantname);
        $additionalsql .= " and (tok.tokenval=$sqlwantname)";
    } // if

    find_token($additionalsql, $wantname);
} // find_tokenvalue


$queryfuncs['entrypoint'] = 'find_entrypoint';
function find_entrypoint($wantname)
{
    global $extflags_public;

    $sql = 'select ent.entrypointname as entrypointname,' .
           ' ext.extname as extname' .
           ' from alextreg_entrypoints as ent' .
           ' left outer join alextreg_extensions as ext' .
           ' on ent.extid=ext.id where (1=1)';

    if (!is_authorized_vendor())
        $sql .= " and (ext.flags & $extflags_public)";

    if ($wantname)
    {
        $sqlwantname = db_escape_string($wantname);
        $sql .= " and (ent.entrypointname='$sqlwantname')";
    } // if

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...
    else
        render_entrypoint_list($wantname, $query);

    db_free_result($query);
} // find_entrypoint


$queryfuncs['anything'] = 'find_anything';
function find_anything($wantname)
{
    find_extension($wantname);
    find_tokenname($wantname);
    find_tokenvalue($wantname);
    find_entrypoint($wantname);
} // find_anything


function do_find($wanttype, $wantname = NULL)
{
    global $queryfuncs;

    $queryfunc = $queryfuncs[$wanttype];
    if (!isset($queryfunc))
    {
        write_error('Invalid search type.');
        return;
    } // if

    $queryfunc($wantname);

    echo "\n<hr>\n";
} // do_find


$operations['op_findone'] = 'op_findone';
function op_findone()
{
    $wanttype = $_REQUEST['wanttype'];
    $wantname = $_REQUEST['wantname'];
    write_debug("called op_findone($wanttype, $wantname)");

    if ( (empty($wantname)) or (empty($wanttype)) )
    {
        write_error('Please fill out all fields.');
        return;
    } // if

    do_find($wanttype, $wantname);
} // op_findone


function show_one_extension($extrow)
{
    global $extflags_public;

    $extname = $extrow['extname'];
    $extid = $extrow['id'];
    $wikiurl = get_alext_wiki_url($extname);
    $htmlextname = htmlentities($extname, ENT_QUOTES);
    echo "<p>$htmlextname (<a href='${wikiurl}'>docs</a>)\n";

    echo "<p><font size='-1'>\n";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;Registered on ${extrow['entrydate']}<br>\n";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;Last edited on ${extrow['lastedit']}<br>\n";
    echo "</font>\n";

    echo "<p>Tokens:\n<ul>\n";

    $sql = 'select * from alextreg_tokens as tok' .
           ' left outer join alextreg_extensions as ext' .
           ' on tok.extid=ext.id';

    if (!is_authorized_vendor())
        $sql .= " where (ext.flags & $extflags_public)";

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // uh...?
    else if (db_num_rows($query) == 0)
        echo "  <li> (no tokens.)\n";
    else
    {
        while ( ($row = db_fetch_array($query)) != false )
        {
            $hex = sprintf("0x%X", $row['tokenval']);  // !!! FIXME: faster way to do this?
            echo "  <li> ${row['tokenname']} ($hex)";
            //echo " added ${row['entrydate']},";
            //echo " last modified ${row['lastedit']}";
            echo "\n";
        } // while
    } // else
    db_free_result($query);

    if (is_authorized_vendor())
    {
        echo "  <li>\n<form>\n";
        echo "<b>Vendor:</b>\n";
        echo "Add a new token named <input type='text' name='tokname'>\n";
        echo "with the value <input type='text' name='tokval'>.\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_addtoken'>\n";
        echo "<input type='submit' name='form_submit' value='Go!'>\n";
        echo "</form>\n";
    } // if

    echo "</ul>\n";

    echo "<p>Entry points:\n<ul>\n";
    $sql = 'select * from alextreg_entrypoints as ent' .
           ' left outer join alextreg_extensions as ext' .
           ' on ent.extid=ext.id';

    if (!is_authorized_vendor())
        $sql .= " where (ext.flags & $extflags_public)";

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // uh...?
    else if (db_num_rows($query) == 0)
        echo "  <li> (no entry points.)\n";
    else
    {
        while ( ($row = db_fetch_array($query)) != false )
        {
            echo "  <li> ${row['entrypointname']}";
            //echo " added ${row['entrydate']},";
            //echo " last modified ${row['lastedit']}";
            echo "\n";
        } // while
    } // else
    db_free_result($query);

    if (is_authorized_vendor())
    {
        echo "  <li>\n<form>\n";
        echo "<b>Vendor:</b>\n";
        echo "Add a new entry point named <input type='text' name='entrypointname'>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_addentrypoint'>\n";
        echo "<input type='submit' name='form_submit' value='Go!'>\n";
        echo "</form>\n";
    } // if

    echo "</ul>\n";

    echo "<hr>\n";
} // show_one_extension


$operations['op_findall'] = 'op_findall';
function op_findall()
{
    $wanttype = $_REQUEST['wanttype'];
    write_debug("called op_findall($wanttype)");
    do_find($wanttype);
} // op_findall


function do_showext($extname)
{
    global $extflags_public;

    $sqlextname = db_escape_string($extname);
    $sql = "select * from alextreg_extensions" .
           " where extname='$sqlextname'";

    if (!is_authorized_vendor())
        $sql .= " and (flags & $extflags_public)";

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...
    else if (db_num_rows($query) == 0)
        write_error('No such extension.');
    else
    {
        // just in case there's more than one for some reason...
        while ( ($row = db_fetch_array($query)) != false )
            show_one_extension($row);
    } // else

    db_free_result($query);
} // do_showext


$operations['op_showext'] = 'op_showext';
function op_showext()
{
    $extname = $_REQUEST['extname'];
    if (empty($extname))
    {
        write_error('No extension specified.');
        return;
    } // if

    do_showext($extname);
} // op_showext


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
    <option selected value="extension">extensions</option>
    <option value="tokenname">tokens</option>
    <option value="entrypoint">entry points</option>
  </select>.
  <input type="hidden" name="operation" value="op_findall">
  <input type="submit" name="form_submit" value="Go!">
</form>

EOF;

} // render_search_ui

?>