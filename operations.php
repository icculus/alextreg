<?php

// modules include this file then add their own handlers:
//  like: $operations['op_blow'] = 'blow_handler';
//  function blow_handler() { blah(); }

$operations = array();

function do_operation()
{
    global $operations;

    $op = $_REQUEST['operation'];
    if (empty($op))
        return;  // nothing to do.

    $func = $operations[$op];
    if (empty($func))
        echo "<p><center><font color='#FF0000'>Bad operation</font></center>\n";
    else
        $func();  // make the call.
} // do_operation

?>
