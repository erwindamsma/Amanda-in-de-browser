<?php
    if (isset($_REQUEST['logout']) && isset($_SESSION)) {
        session_destroy();
        session_start();
    }
    include("SDKs/facebook.php");
    include("SDKs/google.php");
?>