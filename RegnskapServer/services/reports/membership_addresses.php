<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");

$year = array_key_exists("year", $_REQUEST) ? $_GET["year"] : 2007;
$action = array_key_exists("action", $_REQUEST) ? $_GET["action"] : "json";
$db = new DB();

if (!$year) {
	$standard = new AccountStandard($db);
	$year = $standard->getOneValue("STD_YEAR");
}

$accYearMem = new AccountYearMembership($db);

$users = $accYearMem->getReportUsersFull($year);

switch ($action) {
	case "json" :
        echo json_encode($users);
		break;
	case "spreadsheet" :
        header('Content-type: octet-stream');
        header('Content-Disposition: attachment; filename="memberaddresses.csv"');
        foreach($users as $one) {
            
        	if($one["address"]) {
        		echo $one["firstname"].";".$one["lastname"].";".$one["address"].";".$one["postnmb"].";".$one["city"].";".$one["email"].";".$one["birthdate"].";".$one["cellphone"].";".$one["phone"].";\n"; 
        	}
        } 
		break;
}
?>