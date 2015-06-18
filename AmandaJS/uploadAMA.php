<?php
/**
 * Created by PhpStorm.
 * User: Jens
 * Date: 1-6-2015
 * Time: 15:39
 *
 * Used to upload a local file to be displayed in the functionEditor
 */

$target_dir = "tmpfiles/".session_id()."/";
$target_file = $target_dir . "uploaded_" . basename($_FILES["uploadFile"]["name"]);
$uploadOk = 1;
$uploadedFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check file size
if ($_FILES["uploadFile"]["size"] > 1024000) {
    DIE("file is too large.");
}

// Check if file file has a .ama extension.
if(isset($_FILES["uploadFile"])) {
    if($uploadedFileType == "ama" || $uploadedFileType == "txt")
    {
        if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $target_file)) {
            echo "OK:";
            readfile($target_file); //append the file's content to the response
            unlink($target_file); // remove the temp file.
        } else {
            echo "There was an error uploading your file.";
        }


    }
}

?>
