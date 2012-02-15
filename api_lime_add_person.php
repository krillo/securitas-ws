<?php
$firstname =  $_POST['firstname'];
$familyname =  $_POST['familyname'];
$cellphone =  $_POST['cellphone'];
$email =  $_POST['email'];
$admin =  $_POST['admin'];
$lc =  $_POST['lc'];
$portal =  $_POST['portal'];
$idcompany = $_POST['idcompany'];
$companyname = $_POST['companyname'];
$position = $_POST['position'];
$ended = $_POST['ended'];
require_once(__DIR__ . '/../../../wp-config.php');
require_once('securitas-ws.php');
$sws = new SecuritasWS();
$sws->insertPerson($firstname,$familyname,$cellphone,$email, -1, $admin, $lc, $lc, $portal, $idcompany, $companyname, $position, 0, '0');  //always set ended to 0 when its an "add person"