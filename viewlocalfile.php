<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';

$id = $_GET['id'];
$path = DOCUMENTS_FOLDER.$id;

//for security reason : to avoid being able to see all file of system
if(\Main\Utils::contains($id, '..')) die('cannot contain ".." char');
if(!file_exists($path)) die("file $path doesnt exist");

$contentType = mime_content_type($path);

header('Content-Type: '.$contentType);
echo file_get_contents($path);