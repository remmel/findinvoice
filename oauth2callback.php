<?php
require_once __DIR__.'/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->setAccessType('offline');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);

//find scope here : https://developers.google.com/oauthplayground/
$client->addScope([
    Google_Service_Drive::DRIVE_METADATA,
    Google_Service_Drive::DRIVE_FILE,
    Google_Service_Drive::DRIVE
]);

if (! isset($_GET['code'])) {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/debug.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}