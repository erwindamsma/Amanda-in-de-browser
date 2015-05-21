<?php
/**
 * Created by PhpStorm.
 * User: Jens
 * Date: 12-5-2015
 * Time: 13:36
 */

session_start();

$fileUrlLocation = "AmandaJS/";

if(isset($_POST['editorValue']) && isset($_POST['fileName']))
{
    $fileName = "tmpfiles/".session_id()."/".$_POST['fileName'].".ama";
    $filedata = $_POST['editorValue'];

    if (!file_exists('tmpfiles/'.session_id())) {
        mkdir('tmpfiles/'.session_id(), 0777, true);
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

    die("OK:".$_SERVER['HTTP_HOST'].$dir.$fileUrlLocation."downloadama.php?filename=".$_POST['fileName'].".ama");
}
else{
    die("ERROR");
}

?>