<?php


/*
 * Created on Jun 2, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrust.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";


$db = new DB();
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
        
		break;
}
?>
