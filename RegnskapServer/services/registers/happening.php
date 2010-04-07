<?php


/*
 * Created on May 19, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounthappening.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$description = array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : "";
$linedesc = array_key_exists("linedesc", $_REQUEST) ? $_REQUEST["linedesc"] : "";
$debetpost = array_key_exists("debetpost", $_REQUEST) ? $_REQUEST["debetpost"] : "";
$kredpost = array_key_exists("kredpost", $_REQUEST) ? $_REQUEST["kredpost"] : "";
$count_req = array_key_exists("count_req", $_REQUEST) ? $_REQUEST["count_req"] : "";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


switch ($action) {
	case "all" :
		$accHapp = new AccountHappening($db);
		$columnList = $accHapp->getAll();
		echo json_encode($columnList);
		break;
	case "save" :
        $regnSession->checkWriteAccess();

		$accHapp = new AccountHappening($db);
		$accHapp->setId($id);
		$accHapp->setDescription($description);
		$accHapp->setLinedesc($linedesc);
		$accHapp->setDebetpost($debetpost);
		$accHapp->setkredpost($kredpost);
		$accHapp->setCount_req($count_req);
		if (!$id) {
			$accHapp->save();
			echo json_encode($accHapp);
		} else {
			echo $accHapp->save();
		}
		break;
}
?>
