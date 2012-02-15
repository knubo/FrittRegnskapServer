<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/admin/backup_admin.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "";
$viewFile = array_key_exists("viewFile", $_REQUEST) ? $_REQUEST["viewFile"] : "";

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

        echo json_encode(BackupAdmin::upload($prefix, $_FILES));

        break;

    case "view":
        $prefix = $regnSession->getPrefix() . "/";
        echo BackupAdmin::viewFile($prefix, $viewFile);
}


?>