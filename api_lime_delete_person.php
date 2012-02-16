<?php
$idperson =  $_POST['idperson'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$lime = new SecuritasWS();
$lime->deletePerson($idperson);