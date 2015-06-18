<?php
    require_once "dropbox-sdk/lib/Dropbox/autoload.php";
    use \Dropbox as dbx;

	//Start session if it isn't set
    if (!isset($_SESSION)){
        session_start();
    }

    function getWebAuth(){
        $appInfo = dbx\AppInfo::loadFromJsonFile("SDKs/config.json"); //Load key and secret
        $clientIdentifier = "AmandaJS/1.0"; //Name for the client
        $redirectUri = "https://edamsma.nl/amanda"; //Uri to redirect to after the dropbox authentication has been completed
        $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token'); //ArrayEntryStorage for the Dropbox token
        return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore); //Return the web authenticator created with the above variables
    }

	//Logout if if $_REQUEST['logout'] is set and $_SESSION is set
    if (isset($_REQUEST['logout']) && isset($_SESSION)) {
        session_destroy();
        session_start();
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

	//When the user returns from the Dropbox login page: get the users information and fill the session
    if (isset($_GET['state']) && isset($_GET['code'])){
        try {
            list($accessToken, $userId) = getWebAuth()->finish($_GET);

            $dbxClient = new dbx\Client($accessToken, "AmandaJS/1.0"); //Create the Dropbox Client
            $accountInfo = $dbxClient->getAccountInfo(); //Get the users information

			//Save some objects in the session for later use
            $_SESSION['loggedIn'] = true;
            $_SESSION['accessToken'] = $accessToken;
            $_SESSION['dbxClient'] = $dbxClient;
            $_SESSION['accountInfo'] = $accountInfo;

			//Clear the url from GET requests
            $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
        }
        catch (dbx\WebAuthException_BadRequest $ex) {
            error_log("/dropbox-auth-finish: bad request: " . $ex->getMessage());
            // Respond with an HTTP 400 and display error page...
        }
        catch (dbx\WebAuthException_BadState $ex) {
            // Auth session expired.  Restart the auth process.
            header('Location: ' . getWebAuth()->start());
        }
        catch (dbx\WebAuthException_Csrf $ex) {
            error_log("/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage());
            // Respond with HTTP 403 and display error page...
        }
        catch (dbx\WebAuthException_NotApproved $ex) {
            error_log("/dropbox-auth-finish: not approved: " . $ex->getMessage());
        }
        catch (dbx\WebAuthException_Provider $ex) {
            error_log("/dropbox-auth-finish: error redirect from Dropbox: " . $ex->getMessage());
        }
        catch (dbx\Exception $ex) {
            error_log("/dropbox-auth-finish: error communicating with Dropbox API: " . $ex->getMessage());
        }
    } else {
        $authorizeUrl = getWebAuth()->start(); //Create the URL for the login button
    }


?>