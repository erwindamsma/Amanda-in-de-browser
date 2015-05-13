<?php
/**
 * Created by PhpStorm.
 * User: Jens
 * Date: 12-5-2015
 * Time: 13:36
 */

session_start();

$fileUrlLocation = "AmandaJs/";

if(isset($_POST['editorValue']))
{
    $fileName = "tmpfiles/".session_id().".ama";
    $filedata = $_POST['editorValue'];

    if (!file_exists('tmpfiles')) {
        mkdir('tmpfiles', 0777, true);
    }

    $tmpFile = fopen($fileName, "w") or die("ERROR");;
    fwrite($tmpFile, $filedata);
    fclose($tmpFile);

    $url = $_SERVER['REQUEST_URI']; //returns the current URL
    $parts = explode('/',$url);
    $dir = "";
    for ($i = 0; $i < count($parts) - 2; $i++) {
        $dir .= $parts[$i] . "/";
    }

    die("OK:".$_SERVER['HTTP_HOST'].$dir.$fileUrlLocation.$fileName);
}
else{
    die("ERROR");
}

?>