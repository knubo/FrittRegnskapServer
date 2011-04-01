<?php 

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountbelonging.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2005;
$month = array_key_exists("month", $_REQUEST) ? $_REQUEST["month"] : 12;

$db = new DB(false);
$regnSession = new RegnSession($db);
$regnSession->auth();

$rep = new AccountBelonging($db);

$sum = 0;
$data = $rep->listAllByResponsible();
include("views/view_report_belongings_owners.php");

?>