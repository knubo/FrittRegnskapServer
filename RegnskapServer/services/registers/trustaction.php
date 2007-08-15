<?php


/*
 * Created on Jun 22, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrustaction.php");
include_once ("../../classes/accounting/accounttrust.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : null;
$trust = array_key_exists("trust", $_REQUEST) ? $_REQUEST["trust"] : null;
$description = array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : null;
$defaultdesc = array_key_exists("defaultdesc", $_REQUEST) ? $_REQUEST["defaultdesc"] : null;
$clubaction = array_key_exists("clubaction", $_REQUEST) ? $_REQUEST["clubaction"] : null;
$trustaction = array_key_exists("trustaction", $_REQUEST) ? $_REQUEST["trustaction"] : null;
$debetpost = array_key_exists("debetpost", $_REQUEST) ? $_REQUEST["debetpost"] : null;
$creditpost = array_key_exists("creditpost", $_REQUEST) ? $_REQUEST["creditpost"] : null;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


switch ($action) {
	case "all" :
        $accountAction = new AccountTrustAction($db);
        $accTrust = new AccountTrust($db);
		$res = array ();
		$res["actions"] = $accountAction->getAll();
		$res["types"] = $accTrust->getFondtypes();

		echo json_encode($res);
		break;
	case "save" :
       $accountAction = new AccountTrustAction($db, $trust,$description, $defaultdesc, $clubaction, $trustaction, $debetpost, $creditpost, $id);
       $res = $accountAction->save();
       
       if($res) {
       	    echo json_encode($accountAction);
       } else {
       	    echo "0";
       }
       break;
	default :
		die("Unknown action" + $action);
		break;
}
?>
