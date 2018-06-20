<?php
require_once __DIR__.'/vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';

session_start();

//all info : https://developers.google.com/api-client-library/php/auth/web-app


if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    print_r(json_encode(\Main\Main::filesGdrive(new DateTime('2018-05'), $_SESSION['access_token'])));
} else {
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
