<?php
$idperson =  $_POST['idperson'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$sws = new SecuritasWS();
$sws->deletePerson($idperson);