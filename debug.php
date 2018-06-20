<?php
require_once __DIR__.'/vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';

session_start();

//all info : https://developers.google.com/api-client-library/php/auth/web-app

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $accessToken = $_SESSION['access_token'];
//    print_r($accessToken);

    $client = new Google_Client();
    $client->setAuthConfigFile('client_secrets.json');
    $client->setAccessToken($accessToken);
    $client->setAccessType('offline'); //to refresh?
    $isExpired = $client->isAccessTokenExpired();

    if($isExpired) {
//        print_r($accessToken);
//        $to = $client->refreshToken($accessToken->access_token);
        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));

    }

    $file = new \Main\FileAdapterGoogleDrive(DOCUMENTS_FOLDER_GDRIVE, $accessToken);

    print_r(json_encode($file->files(new DateTime('2018-05'))));
} else {
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
