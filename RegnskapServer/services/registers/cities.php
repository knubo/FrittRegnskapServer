<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrackaccount.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/admin/CityAddress.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$dbc = new DB(0, AppConfig::DB_HASH_POSTCODES);

$cityAddress = new CityAddress($dbc);

echo json_encode($cityAddress->find($_REQUEST["zip"], $_REQUEST["street"]))


?>