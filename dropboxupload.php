<?php
	//Get the Dropbox SDK
	use \Dropbox as dbx;
	
    //Include the Dropbox authentication file (this has to be included in every page where Dropbox is used or where the user can login or logout)
	include 'SDKs/dropbox.php';

    $fileToSave = fopen($_SESSION['accountInfo']['uid'] . $_POST['fileName'], 'w'); //Create file to store the document in, in write mode
    fwrite($fileToSave, $_POST['editorValue']); //Write to the file which has just been created
    $fileToSave = fopen($_SESSION['accountInfo']['uid'] . $_POST['fileName'], 'r'); //Open the file which the document is stored in, in read mode
    $uploadResult = $_SESSION['dbxClient']->uploadFile("/" . $_POST['fileName'] . ".ama", dbx\WriteMode::add(), $fileToSave); //Upload to the users Dropbox
    fclose($fileToSave); //Close the file
    unlink($_SESSION['accountInfo']['uid'] . $_POST['fileName']); //Delete the file from the server
?>