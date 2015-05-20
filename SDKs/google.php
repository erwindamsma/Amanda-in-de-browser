<?php
    require_once realpath(dirname(__FILE__) . '/../SDKs/google-api-php-client-master/src/Google/autoload.php');

    $client_id = '341910032596-stmpjksv7lshi2hbcfqkm4fkaf8hhcjv.apps.googleusercontent.com';
    $client_secret = 'O6tKXL4MdoRL7ch6bIMERkmm';
    $redirect_uri = 'http://www.edamsma.nl/?googleLogin';

    $client = new Google_Client();
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    $client->setRedirectUri($redirect_uri);
    $client->setScopes(array('profile', 'email', 'openid',));
    $oauth2 = new Google_Service_Oauth2($client);

    if (isset($_GET['code']) && isset($_GET['googleLogin'])) {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
    } else {
        $authUrl = $client->createAuthUrl();
    }

    //Added code for managing session
    if ($client->getAccessToken()) {
        $googleUser = $oauth2->userinfo->get();

        $db = new mysqli("10.184.18.211", "u151188_auth", "BX}5Z+2x7y");
        $db->select_db("db151188_main");
        $sql = "SELECT userId FROM users WHERE googleId = " . $googleUser->getId();
        $result = $db->query($sql);
        if ($result->num_rows === 0) {
            $db->query("INSERT INTO users (googleId) VALUES (" . $googleUser->getId() . ")");
        }

        $_SESSION['loggedIn'] = true;
        $_SESSION['userId'] = $db->query("SELECT userId FROM users WHERE googleId = ");
        $_SESSION['apiId'] = $googleUser->getId();
        $_SESSION['firstName'] = $googleUser->getGivenName();
        $_SESSION['lastName'] = $googleUser->getFamilyName();
        $_SESSION['email'] = filter_var($googleUser['email'], FILTER_SANITIZE_EMAIL);
        $_SESSION['img'] = filter_var($googleUser['picture'], FILTER_VALIDATE_URL);
    }
?>