<?php

require_once '../common.php';
require_once '../operations.php';
require_once '../headerandfooter.php';
require_once '../listandsearch.php';

$operations['op_addtoken'] = 'op_addtoken';
function op_addtoken()
{
    if (!is_authorized_vendor())
    {
        write_error("You shouldn't be here!");
        return;
    } // if

    $tokname = $_REQUEST['tokname'];
    if (empty($tokname))
    {
        write_error('No token name specified.');
        return;
    } // if

    $extid = $_REQUEST['extid'];
    if (empty($extid))
    {
        write_error('No extension id specified.');
        return;
    } // if

    $tokval = $_REQUEST['tokval'];
    if (empty($tokval))
    {
        write_error('No token value specified.');
        return;
    } // if

    // see if it's already in the database...
    $sqltokname = db_escape_string($tokname);
    $sql = "select id from alextreg_tokens where tokenname='$sqltokname'";
    $query = do_dbquery($sql);
    if ($query == false)
        return;  // error output is handled in database.php ...

    if (db_num_rows($query) > 0)
    {
        write_error('This token name is in use. Below is what a search turned up.');
        render_token_list($tokname, $query);
        db_free_result($query);
        return;
    } // if

    db_free_result($query);

    // Just a small sanity check.
    $cookie = $_REQUEST['iamsure'];
    if ((!empty($cookie)) and ($cookie == $_SERVER['REMOTE_ADDR']))
    {
        $sqlauthor = db_escape_string($_SERVER['REMOTE_USER']);
        $sqltokval = db_escape_string($tokval);
        $sqlextid = db_escape_string($extid);
        // ok, add it to the database.
        $sql = "insert into alextreg_tokens" .  // !!! FIXME: Should have author associated with it!
               " (tokenname, tokenval, extid, author, entrydate, lastedit)" .
               " values ('$sqltokname', $sqltokval, $sqlextid, '$sqlauthor', NOW(), NOW())";
        if (do_dbinsert($sql) == 1)
            echo "<font color='#00FF00'>Token added.</font><br>\n";
    } // if
    else   // put out a confirmation...
    {
        $htmlextid= htmlentities($extid, ENT_QUOTES);
        $htmltokname = htmlentities($tokname, ENT_QUOTES);
        $htmltokval = htmlentities($tokval, ENT_QUOTES);

        $hex = '';
        if (sscanf($tokval, "0x%X", &$dummy) != 1)
            $hex = sprintf(" (0x%X hex)", $tokval);  // !!! FIXME: faster way to do this?

        echo "About to add an extension named $htmlname,<br>\n";
        echo "with value ${htmltokval}${hex}.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='operation' value='op_addtoken'>\n";
        echo "<input type='hidden' name='tokname' value='$htmltokname'>\n";
        echo "<input type='hidden' name='tokval' value='$htmltokval'>\n";
        echo "<input type='hidden' name='extid' value='$htmlextid'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_addtoken


$operations['op_addentrypoint'] = 'op_addentrypoint';
function op_addentrypoint()
{
    if (!is_authorized_vendor())
    {
        write_error("You shouldn't be here!");
        return;
    } // if

    $entname = $_REQUEST['entrypointname'];
    if (empty($entname))
    {
        write_error('No entry point name specified.');
        return;
    } // if

    $extid = $_REQUEST['extid'];
    if (empty($extid))
    {
        write_error('No extension id specified.');
        return;
    } // if

    // see if it's already in the database...
    $sqlentname = db_escape_string($entname);
    $sql = "select id from alextreg_entrypoints where entrypointname='$sqlentname'";
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
        $sqlextid = db_escape_string($extid);
        // ok, add it to the database.
        $sql = "insert into alextreg_entrypoints" .
               " (entrypointname, extid, author, entrydate, lastedit)" .
               " values ('$sqlentname', $sqlextid, '$sqlauthor', NOW(), NOW())";
        if (do_dbinsert($sql) == 1)
            echo "<font color='#00FF00'>Entry point added.</font><br>\n";
    } // if
    else   // put out a confirmation...
    {
        $htmlextid= htmlentities($extid, ENT_QUOTES);
        $htmlentname = htmlentities($entname, ENT_QUOTES);

        echo "About to add an entry point named '$htmlentname'<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n";
        echo "<form>\n";
        echo "<input type='hidden' name='operation' value='op_addentrypoint'>\n";
        echo "<input type='hidden' name='entrypointname' value='$htmlentname'>\n";
        echo "<input type='hidden' name='extid' value='$htmlextid'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
        echo "</form>\n";
    } // else
} // op_addentrypoint


$operations['op_addextension'] = 'op_addextension';
function op_addextension()
{
    if (!is_authorized_vendor())
    {
        write_error("You shouldn't be here!");
        return;
    } // if

    $wantname = $_REQUEST['wantname'];
    if (empty($wantname))
    {
        write_error('No extension name specified.');
        return;
    } // if

    // see if it's already in the database...
    $sqlwantname = db_escape_string($wantname);
    $sql = "select id from alextreg_extensions where extname='$sqlwantname'";
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
               " (extname, flags, author, entrydate, lastedit)" .
               " values ('$sqlwantname', 0, '$sqlauthor', NOW(), NOW())";
        if (do_dbinsert($sql) == 1)
            echo "<font color='#00FF00'>Extension added.</font><br>\n";
    } // if
    else   // put out a confirmation...
    {
        $htmlname = htmlentities($wantname, ENT_QUOTES);
        echo "About to add an extension named $htmlname.<br>\n";
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


if (!is_authorized_vendor())
    write_error('You need to have basic auth in place here.');
else
{
    render_header();
    do_operation();
    render_search_ui();  // do this even if there was an operation to run.
    render_add_ui();
    render_footer();
} // else

?>

