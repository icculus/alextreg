<?php

require_once 'common.php';

function render_header($title = 'OpenAL Extension Registry')
{
// !!! FIXME: need more here, I guess.
echo <<< EOF
<html><head><title>$title</title></head><body>

EOF;
} // render_header

function render_footer()
{
    // !!! FIXME: need more here, I guess.
    echo "<hr>\n";
    if (is_authorized_user())
        echo "<i>Logged in as: ${_SERVER['REMOTE_USER']}<br>\n";
    echo "</body></html>\n";
} // render_footer

?>
