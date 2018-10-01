<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$action = isset($_GET['action']) ? $_GET['action'] : '';

if (isset($_GET['code'])) {
    $adapter = new App\Legacy\FileAdapterGoogleDrive(null, null);
    $_SESSION['access_token'] = $adapter->authenticateCallback($_GET['code']);
    header('Location: /');
} else if($action === 'logout'){
    $adapter = new App\Legacy\FileAdapterGoogleDrive(null, $_SESSION['access_token']);
    $ret = $adapter->revoke();
    unset($_SESSION['access_token']);
    header('Location: /');
}