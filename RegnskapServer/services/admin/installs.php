<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/admin/installer.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : "";
$secret = array_key_exists("secret", $_REQUEST) ? $_REQUEST["secret"] : "";
$hostprefix = array_key_exists("hostprefix", $_REQUEST) ? $_REQUEST["hostprefix"] : "";
$beta = array_key_exists("beta", $_REQUEST) ? $_REQUEST["beta"] : "";
$quota = array_key_exists("quota", $_REQUEST) ? $_REQUEST["quota"] : "";
$description = array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : "";
$wikilogin = array_key_exists("wikilogin", $_REQUEST) ? $_REQUEST["wikilogin"] : "";

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

if($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:".$regnSession->getPrefix());    
}

$master = new Master($db);

switch($action) {
    case "get":
        
        $dbinfo = AppConfig::db(DB::dbhash($one["hostprefix"]));
        $one = $master->getOneInstallation($id);
        $one["db"] = $dbinfo[3];
        echo json_encode($one);
        break;
    case "save":
        $res = $master->updateInstall($id, $hostprefix, $beta, $quota, $description, $wikilogin);
        echo json_encode(array ("result" => $res));
        break;
    case "list":
        $installs = $master->getAllInstallations();
        
        foreach($installs as &$one) {
            $dbinfo = AppConfig::db(DB::dbhash($one["hostprefix"]));
            $one["db"] = $dbinfo[3];
        }
        
        echo json_encode($installs);
        break;
    case "deleterequest":
        $master->deleteRequest($id);
        echo json_encode(array("status"=> "ok"));        
        break;
    case "delete":
        $master->deleteForm($id, $secret);
        break;
    case "doDelete":
        $master->doDelete($id, $secret);
        break;        
    default:
        die("Unknown action $action");
}


?>