<?php
include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/util/logger.php");
include_once ("../classes/auth/RegnSession.php");
include_once ("../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$category = array_key_exists("category", $_REQUEST) ? $_REQUEST["category"] : "error";
$logaction = array_key_exists("logaction", $_REQUEST) ? $_REQUEST["logaction"] : "";
$message = array_key_exists("message", $_REQUEST) ? $_REQUEST["message"] : "";
$pos = array_key_exists("pos", $_REQUEST) ? $_REQUEST["pos"] : null;

$db = new DB();
$regnSession = new RegnSession($db);
$logger = new Logger($db);
$regnSession->auth();

switch ($action) {
	case "log" :
		$logger->log($category, $logaction, $message);
		echo "Logged";
		break;
	case "list" :
        echo json_encode($logger->list_entries($pos));
		break;
	default :
		echo "Unknown action $action";
		break;
}
?>
