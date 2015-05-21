<?php
    require_once realpath(dirname(__FILE__) . '/../SDKs/google-api-php-client-master/src/Google/autoload.php');

    $client = new Google_Client();
    $client->setClientId($googleClientId);
    $client->setClientSecret($googleClientSecret);
    $client->setRedirectUri('http://www.edamsma.nl/?googleLogin');
    $client->setScopes(array('profile', 'email', 'openid',));
    $oauth2 = new Google_Service_Oauth2($client);

    if (isset($_GET['code']) && isset($_GET['googleLogin'])) {
        $client->authenticate($_GET['code']);
        $googleAccessToken = $client->getAccessToken();
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

    if (isset($googleAccessToken) && $googleAccessToken) {
        $client->setAccessToken($googleAccessToken);

        if ($client->getAccessToken()) {
            $googleUser = $oauth2->userinfo->get();

            $db = new mysqli("10.184.18.211", "u151188_auth", "BX}5Z+2x7y");
            $db->select_db("db151188_main");
            $sql = "SELECT userId FROM users WHERE googleId = " . $googleUser->getId();
            $result = $db->query($sql);
            if ($result->num_rows > 0) {
                $sql = "UPDATE users SET firstName = '" . $googleUser->getGivenName() . "', lastName = '" . $googleUser->getFamilyName() . "', email = '" . filter_var($googleUser['email'], FILTER_SANITIZE_EMAIL) . "', lastLoginDate = NOW() WHERE googleId = " . $googleUser->getId();
                $db->query($sql);
            } else {
                $sql = "INSERT INTO users (googleId, firstName, lastName, email, registrationDate, lastLoginDate)
                    VALUES (" . $googleUser->getId() . ", '" . $googleUser->getGivenName() . "', '" . $googleUser->getFamilyName() . "', '" . filter_var($googleUser['email'], FILTER_SANITIZE_EMAIL) . "', NOW(), NOW())";
                $db->query($sql);
            }

            $_SESSION['loggedIn'] = true;
            $_SESSION['userId'] = $db->query("SELECT userId FROM users WHERE googleId = " . $googleUser->getId());
            $_SESSION['apiId'] = $googleUser->getId();
            $_SESSION['firstName'] = $googleUser->getGivenName();
            $_SESSION['lastName'] = $googleUser->getFamilyName();
            $_SESSION['email'] = filter_var($googleUser['email'], FILTER_SANITIZE_EMAIL);
            $_SESSION['img'] = filter_var($googleUser['picture'], FILTER_VALIDATE_URL);
        }

    } else {
        $authUrl = $client->createAuthUrl();
    }


?>