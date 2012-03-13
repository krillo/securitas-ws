<?php

$username = $_POST['username'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$response;
$response['status'] = 'xxx';
$response['status'] = SecuritasWS::createUserName($username);

//return the result to ajax, write it as json
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($response);
