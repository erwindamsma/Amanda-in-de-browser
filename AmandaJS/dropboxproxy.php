<?php
//Used to make an cross domain ajax call.
//Display the content of a url on local domain.

$url = str_replace(" ", "%20", $_GET['u']);

readfile($url);

?>