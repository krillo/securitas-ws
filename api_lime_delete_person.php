<?php
$idperson =  $_POST['idperson'];
echo 'krillo' . $idperson; 

require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-lime.php');
$lime = new SecuritasWS();
$lime->deletePerson($idperson);