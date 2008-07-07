<?php
include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/accounting/accountstandard.php");
include_once ("../classes/util/backupdb.php");
include_once ("../classes/util/logger.php");
include_once ("../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "info";
$table = array_key_exists("table", $_REQUEST) ? $_REQUEST["table"] : "";

$db = new DB();
$regnSession = new RegnSession($db);
$backup = new BackupDB($db);
$regnSession->auth();
$acStandard = new AccountStandard($db);

switch ($action) {
	case "tables" :
		echo json_encode($backup->tables());
		break;

	case "init" :
		$acStandard->setValue("BACKUP_TIME", date("m.d.y H:i"));
		/* Fallthrough */
	case "delete" :
		$path = "../backup/";
		$handle = opendir($path);
		for (; false !== ($file = readdir($handle));)
			if ($file != "." && $file != "..")
				unlink($path . $file);
		closedir($handle);

		$res = array ();
		$res["result"] = 1;
		echo json_encode($res);
		break;
	case "backup" :
		$res = array ();
		$res["result"] = json_encode($backup->backup($table));
		echo json_encode($res);
		break;
	case "info" :
		$res = array ();
		$res["last_backup"] = $acStandard->getOneValue("BACKUP_TIME");

		echo json_encode($res);
		break;
	case "zip" :
		break;
	case "get" :
		break;
}
?>
