<?php
/**
 * Created by PhpStorm.
 * User: Jens
 * Date: 21-5-2015
 * Time: 14:24
 */
session_start();

$fileUrlLocation = "AmandaJS/";

if(isset($_GET['filename']))
{

    header("Content-Disposition: attachment; filename=".$_GET['filename']);
    header("Content-Type:amanda/ama");


    $fileName = "tmpfiles/".session_id()."/".$_GET['filename'];

    $url = $_SERVER['REQUEST_URI']; //returns the current URL
    $parts = explode('/',$url);
    $dir = "";
    for ($i = 0; $i < count($parts) - 2; $i++) {
        $dir .= $parts[$i] . "/";
    }

   // die("OK:".$_SERVER['HTTP_HOST'].$dir.$fileUrlLocation.$fileName);

    $filePath = $_SERVER['HTTP_HOST'].$dir.$fileUrlLocation.$fileName;

    echo readfile("http://". $filePath);

    unlink($fileName);
}


?>