<?php


/*
 * Created on Apr 30, 2007
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "0";
$month = array_key_exists("month", $_REQUEST) ? $_REQUEST["month"] : "0";
$semester = array_key_exists("semester", $_REQUEST) ? $_REQUEST["semester"] : "0";
$email_sender = array_key_exists("email_sender", $_REQUEST) ? $_REQUEST["email_sender"] : "0";
$massletter_due_date = array_key_exists("massletter_due_date", $_REQUEST) ? $_REQUEST["massletter_due_date"] : "0";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accStd = new AccountStandard($db);

switch ($action) {
	case "get" :
		$res = array ();
		$res["year"] = $accStd->getOneValue(AccountStandard::CONST_YEAR);
		$res["month"] = $accStd->getOneValue(AccountStandard::CONST_MONTH);
		$res["semester"] = $accStd->getOneValue(AccountStandard::CONST_SEMESTER);
		$res["email_sender"] = $accStd->getOneValue(AccountStandard::CONST_EMAIL_SENDER);
        $res["massletter_due_date"] = $accStd->getOneValue(AccountStandard::CONST_MASSLETTER_DUE_DATE);

        $accPrices = new AccountMemberPrice($db);
        $prices = $accPrices->getCurrentPrices();
        $res["cost_membership"] = $prices["year"];
        $res["cost_course"] = $prices["course"];
        $res["cost_practice"] = $prices["train"];

        echo json_encode($res);
		break;
	case "save" :
        $regnSession->checkWriteAccess();
		$res = 0;
		$res = $res | $accStd->setValue(AccountStandard::CONST_YEAR, $year);
		$res = $res | $accStd->setValue(AccountStandard::CONST_MONTH, $month);
		$res = $res | $accStd->setValue(AccountStandard::CONST_SEMESTER, $semester);
		$res = $res | $accStd->setValue(AccountStandard::CONST_EMAIL_SENDER, $email_sender);
        $res = $res | $accStd->setValue(AccountStandard::CONST_MASSLETTER_DUE_DATE, $massletter_due_date);
        $report = array();
        $report["result"] = $res ? 1 : 0;

        echo json_encode($report);
		break;
}
?>
