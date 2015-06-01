<?php
    require_once "dropbox-sdk/lib/Dropbox/autoload.php";
    use \Dropbox as dbx;

    if (!isset($_SESSION)){
        session_start();
    }

    function getWebAuth(){
        $appInfo = dbx\AppInfo::loadFromJsonFile("SDKs/config.json");
        $clientIdentifier = "AmandaJS/1.0";
        $redirectUri = "https://edamsma.nl/amanda";
        $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
        return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore);
    }

    if (isset($_REQUEST['logout']) && isset($_SESSION)) {
        session_destroy();
        session_start();
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

    if (isset($_GET['state']) && isset($_GET['code'])){
        try {
            list($accessToken, $userId) = getWebAuth()->finish($_GET);

            $dbxClient = new dbx\Client($accessToken, "AmandaJS/1.0");
            $accountInfo = $dbxClient->getAccountInfo();

            $_SESSION['loggedIn'] = true;
            $_SESSION['accessToken'] = $accessToken;
            $_SESSION['dbxClient'] = $dbxClient;
            $_SESSION['accountInfo'] = $accountInfo;

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
        $authorizeUrl = getWebAuth()->start();
    }


?>