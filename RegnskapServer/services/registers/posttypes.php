<?php


/*
 * Created on Apr 11, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$disableFilter = array_key_exists("disableFilter", $_REQUEST) ? $_REQUEST["disableFilter"] : 0;
$posttype = array_key_exists("posttype", $_REQUEST) ? $_REQUEST["posttype"] : 0;
$use = array_key_exists("use", $_REQUEST) ? $_REQUEST["use"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$acc = new AccountPostType($db);
switch ($action) {
	case "all" :

		$columnList = $acc->getAll($disableFilter);
		echo json_encode($columnList);
		break;
	case "save" :
		break;
	case "use" :
		$colsAffected = $acc->updateInUse($posttype, $use);
		$res = array ();
		$res["result"] = $colsAffected;
        echo json_encode($res);
		break;
    default:
        die("Unknown action $action");
}
?>
