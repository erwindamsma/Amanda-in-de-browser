<?php
    require_once realpath(dirname(__FILE__) . '/../SDKs/google-api-php-client-master/src/Google/autoload.php');

    $client_id = '341910032596-stmpjksv7lshi2hbcfqkm4fkaf8hhcjv.apps.googleusercontent.com';
    $client_secret = 'O6tKXL4MdoRL7ch6bIMERkmm';
    $redirect_uri = 'http://www.edamsma.nl/?googleLogin';

    $client = new Google_Client();
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    $client->setRedirectUri($redirect_uri);
    $client->setScopes(array('https://www.googleapis.com/auth/plus.login', 'profile', 'email', 'openid',));
    $oauth2 = new Google_Service_Oauth2($client);

    if (isset($_GET['code']) && isset($_GET['googleLogin'])) {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
        $_SESSION['loggedIn'] = true;
    } else {
        $authUrl = $client->createAuthUrl();
    }

    if ($client->getAccessToken()) {
        $user = $oauth2->userinfo->get();
        $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
        $img = filter_var($user['picture'], FILTER_VALIDATE_URL);
        $personMarkup = "$email<div><img src='$img?sz=50'></div>";
    }

//    if (isset($_REQUEST['error'])) {
//        echo '<script type="text/javascript">window.close();</script>'; exit;
//    }
//    if ($client->getAccessToken()) {
//        $user = $oauth2->userinfo->get();
//        // These fields are currently filtered through the PHP sanitize filters.
//        $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
//        $img = filter_var($user['picture'], FILTER_VALIDATE_URL);
//        $personMarkup = "$email<div><img src='$img?sz=50'></div>";
//        // The access token may have been updated lazily.
//        $_SESSION['token'] = $client->getAccessToken();
//    } else {
//        $authUrl = $client->createAuthUrl();
//    }
?>