<?php
    define('FACEBOOK_SDK_V4_SRC_DIR', '/../SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/');
    require __DIR__ . '/../SDKs/facebook-php-sdk-v4-4.0-dev/autoload.php';

    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookSession.php' );
    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookRedirectLoginHelper.php' );
    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookRequest.php' );
    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookResponse.php' );
    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookSDKException.php' );
    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookRequestException.php' );
    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookAuthorizationException.php' );
    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/GraphObject.php' );

    require_once( 'SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/Entities/AccessToken.php');

    require_once('SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/HttpClients/FacebookHttpable.php');
    require_once('SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/HttpClients/FacebookCurl.php');
    require_once('SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/HttpClients/FacebookCurlHttpClient.php');
    require_once('SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/FacebookOtherException.php');
    require_once('SDKs/facebook-php-sdk-v4-4.0-dev/src/Facebook/GraphSessionInfo.php');

    use Facebook\FacebookSession;
    use Facebook\FacebookRedirectLoginHelper;
    use Facebook\FacebookRequest;
    use Facebook\FacebookResponse;
    use Facebook\FacebookSDKException;
    use Facebook\FacebookRequestException;
    use Facebook\FacebookAuthorizationException;
    use Facebook\GraphObject;

    FacebookSession::setDefaultApplication('1453083768317435', 'e5aea522d0911aacdac480b23d1755cd');
    $helper = new FacebookRedirectLoginHelper("http://www.edamsma.nl/");
    //$loginUrl = $helper->getLoginUrl();

    try {
        $session = $helper->getSessionFromRedirect();
    } catch (FacebookRequestException $ex) {
        // When Facebook returns an error
    } catch (Exception $ex) {
        // When validation fails or other local issues
    }
    if (isset($session)){
        // graph api request for user data
        $response = (new FacebookRequest( $session, 'GET', '/me' ))->execute();
        // get response
        $graphObject = $response->getGraphObject();

        $db = new mysqli("10.184.18.211", "u151188_auth", "BX}5Z+2x7y");
        $db->select_db("db151188_main");

        $sql = "SELECT userId FROM users WHERE apiId = " . $graphObject->getProperty('id');
        $result = $db->query($sql);
        if ($result->num_rows === 0) {
            $db->query("INSERT INTO users (apiId) VALUES (" . $graphObject->getProperty('id') . ")");
        }

        echo($db->error);
    }
?>