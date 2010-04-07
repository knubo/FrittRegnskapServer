<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");


$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "earningsandcost";

$db = new DB(false);
$regnSession = new RegnSession($db);
$regnSession->auth();

$rep = new ReportYear($db);

if($year == 0) {
    $standard = new AccountStandard($db);
    $year = $standard->getOneValue(AccountStandard::CONST_YEAR);
    
}

switch($action) {
    case "earningsandcost":
        $data = array();
        $data["earnings"] = $rep->list_sums_earnings($year);
        $data["cost"] = $rep->list_sums_cost($year);
        $data["year"] = $year;
        echo json_encode($data);
        break;
}

