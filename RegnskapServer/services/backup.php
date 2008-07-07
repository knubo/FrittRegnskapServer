<?php
include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/util/backupdb.php");
include_once ("../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "tables";
$table = array_key_exists("table", $_REQUEST) ? $_REQUEST["table"] : null;

$db = new DB();
$regnSession = new RegnSession($db);
$backup = new BackupDB($db);
$regnSession->auth();

switch ($action) {
	case "tables" :
		echo json_encode($backup->tables());
		break;
	case "init" :
        echo json_encode($backup->init());
        break;
	case "backup" :
		echo json_encode($backup->backup($table));
		break;
	case "delete" :
		echo json_encode($backup->delete());
		break;
	case "info" :
		echo json_encode($backup->info());
		break;
	case "get" :
		break;
}
?>
