<?php

/*
 * Created on May 1, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountyearmembership.php");
 

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "year";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "2003";
$semester = array_key_exists("semester", $_REQUEST) ? $_REQUEST["semester"] : "0";

$db = new DB();

switch ($action) {
	case "year" :
		$acc = new AccountYearMembership($db);
		echo json_encode($acc->getAllMemberNames($year));
		break;
	case "course" :
		$acc = new AccountSemesterMembership($db, "course");
		echo json_encode($acc->getAllMemberNames($semester));
		break;
	case "train" :
		$acc = new AccountSemesterMembership($db, "train");
		echo json_encode($acc->getAllMemberNames($semester));
		break;
	default :
		die("Unknown action $action");
}
?>
