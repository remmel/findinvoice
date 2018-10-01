<?php

use App\Legacy\Bankin;

require_once __DIR__ . '/../vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';

session_start();

$bank = new Bankin();

$bank->exportBankin();