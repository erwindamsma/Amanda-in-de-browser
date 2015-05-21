<?php
    if (isset($_REQUEST['logout']) && isset($_SESSION)) {
        session_destroy();
        session_start();
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }
    include("Shared/configvars.php");
    include("SDKs/facebook.php");
    include("SDKs/google.php");
?>