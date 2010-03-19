<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/auth/RegnSession.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "json";
$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


if (!$year) {
	$standard = new AccountStandard($db);
	$year = $standard->getOneValue(AccountStandard::CONST_YEAR);
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
        		echo $one["firstname"].";".$one["lastname"].";".$one["address"].";".$one["postnmb"].";".$one["city"].";".$one["email"].";".$one["birthdate"].";".$one["cellphone"].";".$one["phone"].";".$one["gender"].";".$one["id"].";\n";
        	}
        }
		break;
}
?>
