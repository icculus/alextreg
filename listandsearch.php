<?php

require_once 'operations.php';

$operations['op_findone'] = 'op_findone';
function op_findone()
{
    $wanttype = $_REQUEST['wanttype'];
    $wantname = $_REQUEST['wantname'];
    echo "called op_findone($wantname, $wanttype)<br>\n";
}


$operations['op_findall'] = 'op_findall';
function op_findall()
{
    $wanttype = $_REQUEST['wanttype'];
    echo "called op_findall($wanttype)<br>\n";
}



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
