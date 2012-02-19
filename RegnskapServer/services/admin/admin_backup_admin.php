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

$dbBackup = new DB(0, $dbSelect);

switch ($action) {
    case "upload":
        $prefix = $regnSession->getPrefix() . "/";
        $backupAdmin = new BackupAdmin($dbBackup);

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
        break;
    case "install_indexes":
        $installer = new Installer($dbBackup);
        $installer->createIndexes($dbprefix);
        echo "ok";
        break;

    case "install_backup_tables":
        $dbBackup->action("FLUSH TABLES");
        $installer = new Installer($dbBackup);
        $installer->createTables($dbprefix);
        //TODO legge til indexer?
        $installer->createBackupTables($dbprefix);
        echo json_encode(array("status" => 1));
        break;

    case "drop_table":
        $dbBackup->action("drop table if exists $table" . "_backup");
        echo json_encode(array("status" => 1, "table" => "$table" . "_backup"));
        break;

    case "backup_table":
        $backupAdmin = new BackupAdmin($dbBackup);
        $count = $backupAdmin->backupTable($table);
        echo json_encode(array("count" => $count));
        break;

    case "install_from_backup":
        $masterprefix = $regnSession->getPrefix() . "/";

        $backupAdmin = new BackupAdmin($dbBackup);
        $count = $backupAdmin->deleteAndinstallFromBackup($masterprefix, $table);
        echo json_encode(array("count" => $count));
        break;

}

$dbBackup->close();

?>