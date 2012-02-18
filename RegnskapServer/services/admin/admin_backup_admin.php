<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/admin/backup_admin.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/admin/installer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "";
$viewFile = array_key_exists("viewFile", $_REQUEST) ? $_REQUEST["viewFile"] : "";
$dbSelect = array_key_exists("dbSelect", $_REQUEST) ? $_REQUEST["dbSelect"] : "";
$dbprefix = array_key_exists("dbprefix", $_REQUEST) ? $_REQUEST["dbprefix"] : "";
$table = array_key_exists("table", $_REQUEST) ? $_REQUEST["table"] : "";

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

if ($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:" . $regnSession->getPrefix());
}


switch ($action) {
    case "upload":
        $prefix = $regnSession->getPrefix() . "/";

        $db = new DB(0, $dbSelect);

        $backupAdmin = new BackupAdmin($db);

        echo json_encode($backupAdmin->uploadAndAnalyze($prefix, $_FILES));

        break;

    case "init":

        $dbInfo = array();
        for ($i = 1; $i <= DB::DB_COUNT; $i++) {
            $dbInfo[$i] = AppConfig::db($i);
        }
        $dbInfo[-1] = AppConfig::db(-1);

        echo json_encode(array("db" => $dbInfo));
        break;
    case "view":
        $prefix = $regnSession->getPrefix() . "/";
        echo BackupAdmin::viewFile($prefix, $viewFile);

    case "install_indexes":
        $installer = new Installer($db);
        $installer->createIndexes($dbprefix);
        echo "ok";
        break;

    case "install_backup_tables":
        $installer = new Installer($db);
        $installer->createTables($dbprefix);
        //TODO legge til indexer?
        $installer->createBackupTables($dbprefix);
        echo array("status" => 1);
        break;

    case "drop_table":
        $db->action("drop table if exists $table" . "_backup");
        echo array("status" => 1);
        break;

    case "backup_table":
        $backupAdmin = new BackupAdmin($db);
        $count = $backupAdmin->backupTable($table);
        echo json_encode(array("count" => $count));
        break;
}


?>