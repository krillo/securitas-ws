<?php
$idperson =  $_POST['idperson'];
$lc =  $_POST['lc'];
$name =  $_POST['name'];
$lastname =  $_POST['lastname'];
$email =  $_POST['email'];
$phone =  $_POST['phone'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$sws = new SecuritasWS();
$sws->deletePerson($idperson, $lc, $name, $lastname, $email, $phone);