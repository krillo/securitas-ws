<?php
$email =  $_POST['email'];
require_once(__DIR__ . '/../../../../wp-config.php');
$lime = new SecuritasLime();
//$lime->twentyfourEBinsert($email);
$lime->debugOutput();
