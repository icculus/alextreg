<?php

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
