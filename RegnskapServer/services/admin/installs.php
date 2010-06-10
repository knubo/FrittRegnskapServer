<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

if($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:".$regnSession->getPrefix());    
}

switch($action) {
    case "list":
        $master = new Master($db);
        $installs = $master->getAllInstallations();
        
        foreach($installs as &$one) {
            $dbinfo = AppConfig::db(DB::dbhash($one["hostprefix"]));
            $one["db"] = $dbinfo[3];
        }
        
        echo json_encode($installs);
        break;
    case "deleterequest":
        echo "Send email";
        break;
    case "deleteconfirm":
        break;
}


?>