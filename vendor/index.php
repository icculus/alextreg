<?php

require_once '../common.php';
require_once '../operations.php';
require_once '../headerandfooter.php';
require_once '../listandsearch.php';


$operations['op_addextension'] = 'op_addextension';
function op_addextension()
{
    write_error("Not implemented.");
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


if (not defined $_SERVER['REMOTE_USER'])
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

