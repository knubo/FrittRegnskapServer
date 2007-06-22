<?php
/*
 * Created on Jun 22, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrustaction.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";

$db = new DB();
$accountAction = new AccountTrustAction($db);

switch ($action) {
	case "all" :
		echo json_encode($accountAction->getAll());
		break;
	default :
		die("Unknown action" + $action);
		break;
}
?>
