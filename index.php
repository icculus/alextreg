<?php
require_once 'operations.php';
require_once 'headerandfooter.php';
require_once 'listandsearch.php';

render_header();
do_operation();
render_search_ui();  // do this even if there was an operation to run.
render_footer();
?>

