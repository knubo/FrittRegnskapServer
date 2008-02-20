<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/helpers/membersformatter.php");
include_once ("../../classes/accounting/accountbudget.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "init";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "0";
$course = array_key_exists("course", $_REQUEST) ? $_REQUEST["course"] : "0";
$train = array_key_exists("train", $_REQUEST) ? $_REQUEST["train"] : "0";
$keyYear = array_key_exists("keyYear", $_REQUEST) ? $_REQUEST["keyYear"] : "0";
$keyFall = array_key_exists("keyFall", $_REQUEST) ? $_REQUEST["keyFall"] : "0";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$standard = new AccountStandard($db);

$budgetyear = $standard->getOneValue("STD_YEAR") + 1;

switch ($action) {
    case "saveMemberships":
        $accBudget = new AccountBudget($db);
        $res = $accBudget->saveMemberships($keyYear,  $keyFall,  $year, $course, $train);
        $result = array();
        $result["result"] = $res;

        echo json_encode($result);
        break;
	case "init" :

		$result = array ();
		$accYear = new AccountYearMembership($db);
		$accCourse = new AccountSemesterMembership($db, "course");
		$accTrain = new AccountSemesterMembership($db, "train");
		$accPrice = new AccountMemberPrice($db);
		$accBudget = new AccountBudget($db);
        $accSemester = new AccountSemester($db);

		$result["members"] = MembersFormatter :: group($accYear->getOverview(), $accCourse->getOverview(), $accTrain->getOverview(), $accBudget->getMemberships($budgetyear), $accSemester->getForYear($budgetyear));
		$result["price"] = $accPrice->getAll();

		echo json_encode($result);
		break;
}
?>
