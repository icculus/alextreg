<?php

require_once 'common.php';

function render_header($title = 'OpenAL Extension Registry')
{
    $img = ((is_authorized_vendor()) ? '../' : '') . 'openal_title_sm.jpg';

// !!! FIXME: need more here, I guess.
echo <<< EOF
<html><head><title>$title</title></head><body>
<center><img src='$img'><br>OpenAL Extension Registry<hr></center>

EOF;
} // render_header

function render_footer()
{
    // !!! FIXME: need more here, I guess.
    echo "<hr>\n";
    if (is_authorized_vendor())
        echo "<i>Logged in as: ${_SERVER['REMOTE_USER']}<br>\n";
    echo "</body></html>\n";
} // render_footer

?>
