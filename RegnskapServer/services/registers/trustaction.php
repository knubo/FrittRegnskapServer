<?php
/*
 * Created on Jun 22, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrustaction.php");
include_once ("../../classes/accounting/accounttrust.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";

$db = new DB();
$accountAction = new AccountTrustAction($db);
$accTrust = new AccountTrust($db);

switch ($action) {
	case "all" :
        $res = array();
        $res["actions"] = $accountAction->getAll();
        $res["types"] = $accTrust->getFondtypes();
         
		echo json_encode($res);
		break;
	default :
		die("Unknown action" + $action);
		break;
}
?>
