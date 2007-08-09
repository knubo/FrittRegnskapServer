<?php


/*
 * Created on Aug 9, 2007
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$query = array_key_exists("query", $_REQUEST) ? $_REQUEST["query"] : "members";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$standard = new AccountStandard($db);
$year = $standard->getOneValue("STD_YEAR");

switch ($action) {
	case "list" :
		switch ($query) {
			case "members" :
				$accYearMem = new AccountYearMembership($db);
				$users = $accYearMem->getReportUsersFull($year);
				break;
			case "newsletter" :
				break;
		}

		$ret = array ();
		foreach ($users as $one) {
			if (!array_key_exists("email", $one) || !$one["email"]) {
				continue;
			}
			$u = array ();
			$u["name"] = $one["lastname"] . ", " . $one["firstname"];
			$u["email"] = $one["email"];
			$ret[] = $u;
		}

		echo json_encode($ret);
		break;
	case "email" :
		$res = array ();
		$res["status"] = "1";
		sleep(1);
		echo json_encode($res);
		break;
	default :
		die("Unknown action $action.");
}
?>
