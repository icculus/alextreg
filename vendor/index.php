<?php

require_once '../common.php';
require_once '../operations.php';
require_once '../headerandfooter.php';
require_once '../listandsearch.php';


$operations['op_addextension'] = 'op_addextension';
function op_addextension()
{
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
        return;
    } // if

    db_free_result($query);

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

