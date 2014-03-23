<?php
include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/accounting/accountstandard.php");
include_once ("../classes/util/backupdb.php");
include_once ("../classes/util/logger.php");
include_once ("../classes/auth/RegnSession.php");
include_once ("../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "tables";
$table = array_key_exists("table", $_REQUEST) ? $_REQUEST["table"] : "";

$db = new DB();
$regnSession = new RegnSession($db);
$loggedInUser = $regnSession->auth();
$acStandard = new AccountStandard($db);

$prefix = $regnSession->getPrefix();

$backup = new BackupDB($db, $prefix);

date_default_timezone_set(AppConfig::TIMEZONE);

if(!($regnSession->canSeeSecret())) {
    header("HTTP/1.0 513 Validation Error");
    die(json_encode(array("NO_ACCESS_SECRET_ADDRESSES")));
}

switch ($action) {
    case "tables" :
        echo json_encode($backup->tables());
        break;

    case "tables_plain":
        echo preg_replace("/$prefix/","", json_encode($backup->tables()));
        break;

    case "schema_plain":
        echo preg_replace("/XXX_/", "", file_get_contents("../conf/dbschema.sql"));
        echo preg_replace("/XXX_/", "", file_get_contents("../conf/indexes.sql"));
        break;

    case "dump_plain":
    	echo "#Data $table;\n";

		/* Don't send out other people sessions */
    	if($table == "sessions") {
    		break;
    	}

        echo preg_replace("/$prefix/","", $backup->dump_plain($prefix.$table));
        break;

    case "init" :
        if(!file_exists("../backup/$prefix")) {
            mkdir("../backup/$prefix");
        }

        $acStandard->setValue("BACKUP_TIME", date("d.m.Y H:i"));
        $acStandard->setValue("BACKUP_BY", $loggedInUser);
        /* Fallthrough */
    case "delete" :
        $path = "../backup/$prefix/";
        $handle = opendir($path);
        for (; false !== ($file = readdir($handle));) {
            if (strncasecmp($file, ".", 1) != 0) {
                unlink($path . $file);
            }
        }
        closedir($handle);

        $res = array ();
        $res["result"] = 1;
        echo json_encode($res);
        break;
    case "backup" :
        $res = array ();
        if($prefix != "master_" && strncmp($prefix, $table, strlen($prefix)) <> 0) {
            die("No access to table $table as prefix is $prefix");
        }

        $res["result"] = json_encode($backup->backup($table)) ? 1 : 0;
        $res["info"] = json_encode($backup->info);
        echo json_encode($res);
        break;
    case "info" :
        $res = array ();
        $res["last_backup"] = $acStandard->getOneValue("BACKUP_TIME");
        $res["last_backup_by"] = $acStandard->getOneValue("BACKUP_BY");
        if(file_exists("../backup/$prefix/backup.zip")) {
            $res["backup_file"] = date("d.m.Y H:i", filemtime("../backup/$prefix/backup.zip"));
        }
        echo json_encode($res);
        break;
    case "zip" :
        $res = array ();
        $res["result"] = json_encode($backup->zip()) ? 1 : 0;
        echo json_encode($res);
        break;
    case "get" :
        $backupFileName = "backupAccounting".date("d-m-Y").".zip";
        header('Content-type: octet-stream');
        header('Content-Disposition: attachment; filename="'.$backupFileName);
        readfile("../backup/$prefix/backup.zip");
        break;
}
?>