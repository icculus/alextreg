<?php

require_once '../common.php';
require_once '../operations.php';
require_once '../headerandfooter.php';
require_once '../listandsearch.php';

function welcome_here()
{
    if (!is_authorized_vendor())
    {
        // This means you don't have Basic Auth in place, probably.
        write_error("You shouldn't be here!");
        return false;
    } // if

    return true;
} // welcome_here


function update_papertrail($action, $donesql, $extid)
{
    $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
    $sqlsql = db_escape_string($donesql);
    $sqlaction = db_escape_string($action);
    $htmlaction = htmlentities($action, ENT_QUOTES);

    echo "<font color='#00FF00'>$htmlaction</font><br>\n";

    // Update extensions.lastedit field if this involves an extension.
    if (isset($extid))
    {
        $sql = "update alextreg_extensions set lastedit=NOW(), lasteditauthor='$sqlauthor' where id=$extid";
        do_dbupdate($sql);
    } // if

    // Fill in the papertrail.
    $sql = 'insert into alextreg_papertrail' .
           ' (action, sql, author, entrydate)' .
           " values ('$sqlaction', '$sqlsql', '$sqlauthor', NOW())";
    do_dbinsert($sql);
} // update_papertrail


$operations['op_renderpapertrail'] = 'op_renderpapertrail';
function op_renderpapertrail()
{
    $sql = 'select * from alextreg_papertrail order by entrydate';
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    echo "<u>Current paper trail (oldest entries first)...</u>\n";
    echo "<ul>\n";
    
    while ( ($row = db_fetch_array($query)) != false )
    {
        $htmlaction = htmlentities(${row['action']}, ENT_QUOTES);
        $htmlauthor = htmlentities(${row['author']}, ENT_QUOTES);
        $htmlentrydate = htmlentities(${row['entrydate']}, ENT_QUOTES);
        $htmlsql = htmlentities(${row['sql']}, ENT_QUOTES);
        echo "  <li>$htmlaction: $htmlauthor, $htmlentrydate<br>\n";
        echo "      '$htmlsql'\n";
    } // while
    db_free_result($query);

    echo "</ul>\n";
    echo "<p>End of papertrail.\n";
} // op_renderpapertrail


$operations['op_addtoken'] = 'op_addtoken';
function op_addtoken()
{
    if (!welcome_here()) return;
    if (!get_input_string('tokname', 'token name', $tokname)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;
    if (!get_input_int('tokval', 'token value', $tokval)) return;

    // see if it's already in the database...
    $sqltokname = db_escape_string($tokname);
    $sql = 'select tok.*, ext.extname from alextreg_tokens as tok' .
           ' left outer join alextreg_extensions as ext' .
           ' on tok.extid=ext.id' .
           " where (tok.tokenname='$sqltokname') or (tok.tokenval=$tokval)";

    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    if (db_num_rows($query) > 0)
    {
        write_error('This token name or value is in use. Below is what a search turned up.');
        render_token_list(false, $query);
        db_free_result($query);
        return;
    } // if

    db_free_result($query);

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, add it to the database.
        $sql = "insert into alextreg_tokens" .
               " (tokenname, tokenval, extid, author, entrydate, lasteditauthor, lastedit)" .
               " values ('$sqltokname', $tokval, $extid, '$sqlauthor', NOW(), '$sqlauthor', NOW())";
        if (do_dbinsert($sql) == 1)
        {
            update_papertrail("Token '$tokname' added", $sql, $extid);
            do_showext($extname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        $htmltokname = htmlentities($tokname, ENT_QUOTES);

        $hex = '';
        if (sscanf($tokval, "0x%X", &$dummy) != 1)
            $hex = sprintf(" (0x%X hex)", $tokval);  // !!! FIXME: faster way to do this?

        echo "About to add a token named '$htmltokname',<br>\n";
        echo "with value ${tokval}${hex}.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='operation' value='op_addtoken'>\n";
        echo "<input type='hidden' name='tokname' value='$htmltokname'>\n";
        echo "<input type='hidden' name='tokval' value='$tokval'>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_addtoken


$operations['op_addentrypoint'] = 'op_addentrypoint';
function op_addentrypoint()
{
    if (!welcome_here()) return;
    if (!get_input_string('entrypointname', 'entry point name', $entname)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // see if it's already in the database...
    $sqlentname = db_escape_string($entname);
    $sql = "select * from alextreg_entrypoints where entrypointname='$sqlentname'";
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    if (db_num_rows($query) > 0)
    {
        write_error('This entry point is in use. Below is what a search turned up.');
        render_entrypoint_list($tokname, $query);
        db_free_result($query);
        return;
    } // if

    db_free_result($query);

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, add it to the database.
        $sql = "insert into alextreg_entrypoints" .
               " (entrypointname, extid, author, entrydate, lasteditauthor, lastedit)" .
               " values ('$sqlentname', $extid, '$sqlauthor', NOW(), '$sqlauthor', NOW())";
        if (do_dbinsert($sql) == 1)
        {
            update_papertrail("Entry point '$entname' added", $sql, $extid);
            do_showext($extname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlentname = htmlentities($entname, ENT_QUOTES);
        $htmlextname = htmlentities($extname, ENT_QUOTES);

        echo "About to add an entry point named '$htmlentname'<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='operation' value='op_addentrypoint'>\n";
        echo "<input type='hidden' name='entrypointname' value='$htmlentname'>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_addentrypoint


$operations['op_addextension'] = 'op_addextension';
function op_addextension()
{
    if (!welcome_here()) return;
    if (!get_input_string('wantname', 'extension name', $wantname)) return;

    // see if it's already in the database...
    $sqlwantname = db_escape_string($wantname);
    $sql = "select * from alextreg_extensions where extname='$sqlwantname'";
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    if (db_num_rows($query) > 0)
    {
        write_error('This extension name is in use. Below is what a search turned up.');
        render_extension_list($wantname, $query);
        db_free_result($query);
        return;
    } // if

    db_free_result($query);

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, add it to the database.
        $sql = "insert into alextreg_extensions" .
               " (extname, public, author, entrydate, lasteditauthor, lastedit)" .
               " values ('$sqlwantname', 0, '$sqlauthor', NOW(), '$sqlauthor', NOW())";
        if (do_dbinsert($sql) == 1)
        {
            update_papertrail("Extension '$extname' added", $sql, NULL);
            do_showext($wantname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlname = htmlentities($wantname, ENT_QUOTES);
        echo "About to add an extension named '$htmlname'.<br>\n";
        echo "You can add tokens and entry points to this extension in a moment.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='wantname' value='$htmlname'>\n";
        echo "<input type='hidden' name='operation' value='op_addextension'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_addextension


$operations['op_showhideext'] = 'op_showhideext';
function op_showhideext()
{
    if (!welcome_here()) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;
    if (!get_input_bool('newval', 'toggle value', $newval)) return;

    $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
    $sql = "update alextreg_extensions set public=$newval, lastedit=NOW(), lasteditauthor='$sqlauthor' where id=$extid";
    if (do_dbupdate($sql) == 1)
    {
        $pubpriv = ($newval) ? "public" : "private";
        update_papertrail("Extension '$extname' toggled $pubpriv", $sql, NULL);
        do_showext($extname);
    } // if
} // op_showhideext


$operations['op_delext'] = 'op_delext';
function op_delext()
{
    if (!welcome_here()) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, nuke it.
        $sql = "delete from alextreg_extensions where id=$extid";
        if (do_dbdelete($sql) == 1)
        {
            update_papertrail("EXTENSION '$extname' DELETED", $sql, NULL);
            $sql = "delete from alextreg_tokens where extid=$extid";
            $rc = do_dbdelete($sql, -1);
            update_papertrail("DELETED $rc TOKENS", $sql, NULL);
            $sql = "delete from alextreg_entrypoints where extid=$extid";
            $rc = do_dbdelete($sql, -1);
            update_papertrail("DELETED $rc ENTRY POINTS", $sql, NULL);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        echo "About to delete an extension named '$htmlextname'<br>\n";
        echo "<b><font size='+1'>\n";
        echo "THERE IS NO UNDELETE. MAKE SURE YOU <u>REALLY</u> WANT TO DO THIS.<br>\n";
        echo "</font></b>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_delext'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_delext


$operations['op_deltok'] = 'op_deltok';
function op_deltok()
{
    if (!welcome_here()) return;
    if (!get_input_string('tokname', 'token name', $tokname)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqltokname = db_escape_string($tokname);
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, nuke it.
        $sql = "delete from alextreg_tokens where tokenname='$sqltokname'";
        if (do_dbdelete($sql) == 1)
        {
            update_papertrail("TOKEN '$tokname' DELETED", $sql, $extid);
            do_showext($extname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        $htmltokname = htmlentities($tokname, ENT_QUOTES);
        echo "About to delete a token named '$htmltokname'<br>\n";
        echo "<b><font size='+1'>\n";
        echo "THERE IS NO UNDELETE. MAKE SURE YOU <u>REALLY</u> WANT TO DO THIS.<br>\n";
        echo "</font></b>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='tokname' value='$htmltokname'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_deltok'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_deltok


$operations['op_delent'] = 'op_delent';
function op_delent()
{
    if (!welcome_here()) return;
    if (!get_input_string('entname', 'entry point name', $entname)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlentname = db_escape_string($entname);
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, nuke it.
        $sql = "delete from alextreg_entrypoints where entrypointname='$sqlentname'";
        if (do_dbdelete($sql) == 1)
        {
            update_papertrail("ENTRY POINT '$entname' DELETED", $sql, $extid);
            do_showext($extname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        $htmlentname = htmlentities($entname, ENT_QUOTES);
        echo "About to delete an entry point named '$htmlentname'<br>\n";
        echo "<b><font size='+1'>\n";
        echo "THERE IS NO UNDELETE. MAKE SURE YOU <u>REALLY</u> WANT TO DO THIS.<br>\n";
        echo "</font></b>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='entname' value='$htmlentname'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_delent'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_delent


$operations['op_renameext'] = 'op_renameext';
function op_renameext()
{
    if (!welcome_here()) return;
    if (!get_input_string('newval', 'new extension name', $newval)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlnewval = db_escape_string($newval);
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, nuke it.
        $sql = "update alextreg_extensions set extname='$sqlnewval'," .
               " lastedit=NOW(), lasteditauthor='$sqlauthor' where id=$extid";
        if (do_dbupdate($sql) == 1)
        {
            update_papertrail("Extension '$entname' renamed to '$newval'", $sql, NULL);
            do_showext($newval);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlnewval = htmlentities($newval, ENT_QUOTES);
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        echo "About to rename an extension named '$htmlextname' to '$htmlnewval'.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='newval' value='$htmlnewval'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_renameext'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_renameext


$operations['op_renameent'] = 'op_renameent';
function op_renameent()
{
    if (!welcome_here()) return;
    if (!get_input_string('entname', 'current entrypoint name', $entname)) return;
    if (!get_input_string('newval', 'new entrypoint name', $newval)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // see if it's already in the database...
    $sqlnewval = db_escape_string($newval);
    $sql = 'select ent.*, ext.extname from alextreg_entrypoints as ent' .
           ' left outer join alextreg_extensions as ext' .
           ' on ent.extid=ext.id' .
           " where (entrypointname='$sqlnewval')";
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    if (db_num_rows($query) > 0)
    {
        write_error('The new entry point is in use. Below is what a search turned up.');
        render_entrypoint_list($newval, $query);
        db_free_result($query);
        return;
    } // if
    db_free_result($query);

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlentname = db_escape_string($entname);
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, nuke it.
        $sql = "update alextreg_entrypoints set entrypointname='$sqlnewval'," .
               " lastedit=NOW(), lasteditauthor='$sqlauthor'" .
               " where entrypointname='$sqlentname'";
        if (do_dbupdate($sql) == 1)
        {
            update_papertrail("Entry point '$entname' renamed to '$newval'", $sql, $extid);
            do_showext($extname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlnewval = htmlentities($newval, ENT_QUOTES);
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        $htmlentname = htmlentities($entname, ENT_QUOTES);
        echo "About to rename an entry point named '$htmlentname' to '$htmlnewval'.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='newval' value='$htmlnewval'>\n";
        echo "<input type='hidden' name='entname' value='$htmlentname'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_renameent'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_renameent


$operations['op_renametok'] = 'op_renametok';
function op_renametok()
{
    if (!welcome_here()) return;
    if (!get_input_string('tokname', 'current token name', $tokname)) return;
    if (!get_input_string('newval', 'new token name', $newval)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // see if it's already in the database...
    $sqlnewval = db_escape_string($newval);
    $sql = 'select tok.*, ext.extname from alextreg_tokens as tok' .
           ' left outer join alextreg_extensions as ext' .
           ' on tok.extid=ext.id' .
           " where (tokenname='$sqlnewval')";
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    if (db_num_rows($query) > 0)
    {
        write_error('The new token name is in use. Below is what a search turned up.');
        render_token_list($newval, $query);
        db_free_result($query);
        return;
    } // if
    db_free_result($query);

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqltokname = db_escape_string($tokname);
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, nuke it.
        $sql = "update alextreg_tokens set tokenname='$sqlnewval'," .
               " lastedit=NOW(), lasteditauthor='$sqlauthor'" .
               " where tokenname='$sqltokname'";
        if (do_dbupdate($sql) == 1)
        {
            update_papertrail("Token '$tokname' renamed to '$newval'", $sql, $extid);
            do_showext($extname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlnewval = htmlentities($newval, ENT_QUOTES);
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        $htmltokname = htmlentities($tokname, ENT_QUOTES);
        echo "About to rename a token named '$htmltokname' to '$htmlnewval'.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='newval' value='$htmlnewval'>\n";
        echo "<input type='hidden' name='tokname' value='$htmltokname'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_renametok'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_renametok


$operations['op_revaluetok'] = 'op_revaluetok';
function op_revaluetok()
{
    if (!welcome_here()) return;
    if (!get_input_string('tokname', 'token name', $tokname)) return;
    if (!get_input_int('newval', 'new token value', $newval)) return;
    if (!get_input_string('extname', 'extension name', $extname)) return;
    if (!get_input_int('extid', 'extension id', $extid)) return;

    // see if it's already in the database...
    $sqlnewval = db_escape_string($newval);
    $sql = 'select tok.*, ext.extname from alextreg_tokens as tok' .
           ' left outer join alextreg_extensions as ext' .
           ' on tok.extid=ext.id' .
           " where (tokenval=$newval)";
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    if (db_num_rows($query) > 0)
    {
        write_error('The new token value is in use. Below is what a search turned up.');
        render_token_list($tokname, $query);
        db_free_result($query);
        return;
    } // if
    db_free_result($query);

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqltokname = db_escape_string($tokname);
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        // ok, nuke it.
        $sql = "update alextreg_tokens set tokenval=$newval," .
               " lastedit=NOW(), lasteditauthor='$sqlauthor'" .
               " where tokenname='$sqltokname'";
        if (do_dbupdate($sql) == 1)
        {
            update_papertrail("Token '$tokname' revalued to '$newval'", $sql, $extid);
            do_showext($extname);
        } // if
    } // if
    else   // put out a confirmation...
    {
        $htmlnewval = htmlentities($newval, ENT_QUOTES);
        $htmlextname = htmlentities($extname, ENT_QUOTES);
        $htmltokname = htmlentities($tokname, ENT_QUOTES);
        $hex = '';
        if (sscanf($newval, "0x%X", &$dummy) != 1)
            $hex = sprintf(" (0x%X hex)", $newval);  // !!! FIXME: faster way to do this?
        echo "About to change the value of a token named '$htmltokname' to ${newval}${hex}.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='hidden' name='extid' value='$extid'>\n";
        echo "<input type='hidden' name='newval' value='$htmlnewval'>\n";
        echo "<input type='hidden' name='tokname' value='$htmltokname'>\n";
        echo "<input type='hidden' name='extname' value='$htmlextname'>\n";
        echo "<input type='hidden' name='operation' value='op_revaluetok'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_revaluetok


function render_add_ui()
{
    echo <<< EOF

<p>
...or...

<p>
<form method="post" action="${_SERVER['PHP_SELF']}">
  <b>Vendor:</b>
  I want to add a new extension
  named <input type="text" name="wantname" value="">.
  <input type="hidden" name="operation" value="op_addextension">
  <input type="submit" name="form_submit" value="Go!">
</form>

EOF;
} // render_add_ui


if (welcome_here())
{
    render_header();
    if (do_operation())
        echo "<p>Back to <a href='${_SERVER['PHP_SELF']}'>search page</a>.\n";
    else
        render_search_ui();
    render_add_ui();
    render_footer();
} // else

?>

