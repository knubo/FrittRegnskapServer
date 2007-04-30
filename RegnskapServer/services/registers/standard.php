<?php


/*
 * Created on Apr 30, 2007
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "0";
$month = array_key_exists("month", $_REQUEST) ? $_REQUEST["month"] : "0";
$semester = array_key_exists("semester", $_REQUEST) ? $_REQUEST["semester"] : "0";
$cost_course = array_key_exists("cost_course", $_REQUEST) ? $_REQUEST["cost_course"] : "0";
$cost_practice = array_key_exists("cost_practice", $_REQUEST) ? $_REQUEST["cost_practice"] : "0";
$cost_membership = array_key_exists("cost_membership", $_REQUEST) ? $_REQUEST["cost_membership"] : "0";

$db = new DB();
$accStd = new AccountStandard($db);

switch ($action) {
	case "get" :
		$res = array ();
		$res["year"] = $accStd->getOneValue("STD_YEAR");
		$res["month"] = $accStd->getOneValue("STD_MONTH");
		$res["semester"] = $accStd->getOneValue("STD_SEMESTER");
		$res["cost_course"] = $accStd->getOneValue("STD_COURSE_PRICE");
		$res["cost_practice"] = $accStd->getOneValue("STD_TRAIN_PRICE");
		$res["cost_membership"] = $accStd->getOneValue("STD_MEMBERSHIP_PRICE");
		echo json_encode($res);
		break;
	case "save" :
		$res = 0;
		$res = $res || $accStd->setValue("STD_YEAR", $year);
		$res = $res || $accStd->setValue("STD_MONTH", $month);
		$res = $res || $accStd->setValue("STD_SEMESTER", $semester);
		$res = $res || $accStd->setValue("STD_COURSE_PRICE", $cost_course);
		$res = $res || $accStd->setValue("STD_TRAIN_PRICE", $cost_practice);
		$res = $res || $accStd->setValue("STD_MEMBERSHIP_PRICE", $cost_membership);

		echo $res;
		break;
}
?>
