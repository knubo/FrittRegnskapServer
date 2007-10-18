<?php


/*
 * Created on Oct 15, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrackaccount.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accPlan = new AccountTrackAccount($db);

switch ($action) {
	case "all" :
		$all = $accPlan->getAll();

		echo json_encode($all);
		break;
	default :
		die("No action");
}
?>
