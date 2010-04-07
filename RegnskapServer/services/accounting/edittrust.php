<?php


/*
 * Created on Jun 2, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accounttrust.php");
include_once ("../../classes/accounting/accounttrustaction.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";
$actionid = array_key_exists("actionid", $_REQUEST) ? $_REQUEST["actionid"] : 0;
$day = array_key_exists("day", $_REQUEST) ? $_REQUEST["day"] : 0;
$month = array_key_exists("month", $_REQUEST) ? $_REQUEST["month"] : 0;
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$desc = array_key_exists("desc", $_REQUEST) ? $_REQUEST["desc"] : 0;
$attachment = array_key_exists("attachment", $_REQUEST) ? $_REQUEST["attachment"] : 0;
$postnmb = array_key_exists("postnmb", $_REQUEST) ? $_REQUEST["postnmb"] : 0;
$amount = array_key_exists("amount", $_REQUEST) ? $_REQUEST["amount"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accTrust = new AccountTrust($db);

switch ($action) {
	case "status" :
		$data = array ();
		$data["types"] = $accTrust->getFondtypes();

		$fondData = array ();
		$sumfondData = array ();
		$sumclubData = array ();
		foreach ($data["types"] as $fondinfo) {
			$fond = $fondinfo["fond"];
			$fondData[$fond] = $accTrust->getFondInfo($fond);
			$sumfondData[$fond] = $accTrust->getFondSum($fond);
			$sumclubData[$fond] = $accTrust->getFondSum($fond, 1);
		}

		$data["data"] = $fondData;
		$data["sumfond"] = $sumfondData;
		$data["sumclub"] = $sumclubData;
		$data["currentYear"] = 0;
		echo json_encode($data);
		break;
	case "add" :
        $regnSession->checkWriteAccess();
    
		$accTrustAction = new AccountTrustAction($db);
		$accTrustAction->load($actionid);
        $result = array();
        $result["result"] = $accTrustAction->addAccountTrust($day, $month, $year, $desc, $attachment, $postnmb, $amount);
		echo json_encode($result);
        break;
}
?>
