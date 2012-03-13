<?php
$pwd =  $_POST['pwd'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$sws = new SecuritasWS();
$sws->changepassword($pwd);