<?php
$firstname =  $_POST['firstname'];
$familyname =  $_POST['familyname'];
$cellphone =  $_POST['cellphone'];
$email =  $_POST['email'];
$idperson =  $_POST['idperson'];
$admin =  $_POST['admin'];
$lc =  $_POST['lc'];
$original_lc = $_POST['original_lc'];
$portal =  $_POST['portal'];
$idcompany = $_POST['idcompany'];
$companyname = $_POST['companyname'];
$position = $_POST['position'];
$ended = $_POST['ended'];
$wpuserid = $_POST['wpuserid'];
$password = $_POST['password'];
$password2 = $_POST['password2'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$sws = new SecuritasWS();
$sws->updatePerson($firstname,$familyname,$cellphone,$email,$idperson,$admin,$lc,$original_lc,$portal,$idcompany, $companyname, $position, $ended, $wpuserid);
