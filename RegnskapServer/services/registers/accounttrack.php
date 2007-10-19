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
$values = array_key_exists("values", $_REQUEST) ? $_REQUEST["values"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accPlan = new AccountTrackAccount($db);

switch ($action) {
	case "add" :
        $regnSession->checkWriteAccess();
		$db->begin();
		$data = json_decode($values);
		if (!$accPlan->addPosts($data)) {
			$db->rollback();
			$result = 0;
		} else {
			$db->commit();
			$result = 1;
		}

		echo json_encode(array (
			"result" => $result
		));

		break;
	case "remove" :
        $regnSession->checkWriteAccess();
        $db->begin();
		$data = json_decode($values);
		if (!$accPlan->removePosts($data)) {
			$db->rollback();
			$result = 0;
		} else {
			$db->commit();
			$result = 1;
		}

		echo json_encode(array (
			"result" => $result
		));
		break;
	case "all" :
		$all = $accPlan->getAll();

		echo json_encode($all);
		break;
	default :
		die("No action");
}
?>
