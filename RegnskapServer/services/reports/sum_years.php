<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/auth/RegnSession.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2005;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$rep = new ReportYear($db);

$data = $rep->list_sums($year);

include("views/view_report_year.php");


?>
