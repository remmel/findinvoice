<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';

use Main\FetchOvhSoap;

switch ($_GET['id']) {
    case FetchOvhSoap::class:
        $fetch = new FetchOvhSoap();
        break;
}

if(!isset($_GET['invoice'])) {
    $invoiceIds = $fetch->invoicesId(new \DateTime($_GET['date']), (float)$_GET['amount']);
    echo json_encode($invoiceIds);
} else {
    list($tmpPath, $fn) = $fetch->download($_GET['invoice']);

    echo json_encode([
        'tmppath' => $tmpPath,
        'fn' => $fn
    ]);
}
?>