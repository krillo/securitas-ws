<?php
$firstname =  $_POST['firstname'];
$familyname =  $_POST['familyname'];
$cellphone =  $_POST['cellphone'];
$email =  $_POST['email'];
$idperson =  $_POST['idperson'];
$admin =  $_POST['admin'];
$lc =  $_POST['lc'];
$portal =  $_POST['portal'];
$idcompany = $_POST['idcompany'];
$position = $_POST['position'];
$ended = $_POST['ended'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-lime.php');
$lime = new SecuritasWS();
//$lime->updatePerson($firstname,$familyname,$cellphone,$email,$idperson,$admin,$lc,$portal, $idcompany, $position, $ended);
$lime->updatePerson($firstname,$familyname,$cellphone,$email,$idperson,$admin,$lc,$portal, '6016001', $position, $ended);
//$lime->debugOutput();


