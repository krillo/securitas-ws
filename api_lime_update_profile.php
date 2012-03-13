<?php
$firstname =  $_POST['firstname'];
$familyname =  $_POST['familyname'];
$cellphone =  $_POST['cellphone'];
$email =  $_POST['email'];
$position = $_POST['position'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$sws = new SecuritasWS();
$sws->updateProfile($firstname,$familyname,$cellphone,$email,$position);