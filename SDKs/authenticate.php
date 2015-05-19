<?php
    if (isset($_REQUEST['logout'])) {
        session_destroy();
        session_start();
    }
    include("SDKs/facebook.php");
    include("SDKs/google.php");
?>