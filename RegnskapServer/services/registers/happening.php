<?php
/*
 * Created on May 19, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounthappening.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
 
$db = new DB();

switch ($action) {
	case "all" :
		$accHapp = new AccountHappening($db);
		$columnList = $accHapp->getAll();
		echo json_encode($columnList);
		break;
	case "save" :
		$accHapp = new AccountHappening($db);
		echo $accHapp->save();
		break;
}
?>
