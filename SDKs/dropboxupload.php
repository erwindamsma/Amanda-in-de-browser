<?php
/*REMOVE THIS PART BEFORE GOING LIVE AS IT ENABLES PHP ERRORS
   ################################*/
error_reporting(-1);
ini_set('display_errors', 'On');
/*##############################*/


   // use \Dropbox as dbx;
    //include 'dropbox.php';
//Er gaat hier iets mis

    //$fileToSave = fopen();

    echo '<p>Hi I am some random ' . rand() .' output from the server.</p>';
    echo "<br>En de filenaam is ".$_POST['fileName']." en de content is: ".$_POST['editorValue'];
?>