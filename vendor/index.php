<?php

require_once '../common.php';
require_once '../operations.php';
require_once '../headerandfooter.php';
require_once '../listandsearch.php';


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
    $sql = "select id from alextreg_extensions where extname='$wantname'";
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
        do_dbinsert($sql);
    } // if
    else   // put out a confirmation...
    {
        $htmlname = htmlentities($wantname, ENT_QUOTES);
        echo "About to add an extension named $htmlname.<br>\n";
        echo "You can add tokens and entry points to this extension in a moment.<br>\n";
        echo "...if you're sure, click 'Confirm'...<br>\n");
        echo "<form>\n";
        echo "<input type='hidden' name='wantname' value='$htmlname'>\n";
        echo "<input type='hidden' name='operation' value='op_addextension'>\n";
        echo "<input type='hidden' name='iamsure' value='${_SERVER['REMOTE_ADDR']}'>\n";
        echo "<input type='submit' name='form_submit' value='Confirm'>\n";
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


if (empty($_SERVER['REMOTE_USER']))
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

