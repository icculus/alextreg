<?php

// modules include this file then add their own handlers:
//  like: $operations['op_blow'] = 'blow_handler';
//  function blow_handler() { blah(); }

$operations = array();

function do_operation()
{
    global $operations;

    $op = $_POST['operation'];
    if (empty($op))
        return;  // nothing to do.

    // check for registered operation handlers...
    foreach ($operations as $key => $value)
    {
        if (strcasecmp($key, $op) == 0)  // operation to handle.
        {
            $value();  // call the handler...
            return;    //  ...then get the hell out.
        } // if
    } // foreach

   echo "<p><center><font color='#FF0000'>Unexpected operation?!</font></center>\n";
} // do_operation

?>
