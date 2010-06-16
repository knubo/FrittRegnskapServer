<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/util/system.php");
include_once ("../../classes/admin/installer.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "";
$content = array_key_exists("content", $_REQUEST) ? $_REQUEST["content"] : "";

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

if($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:".$regnSession->getPrefix());
}

if($action == "save") {
    if(file_exists(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed.inactive")) {
        file_put_contents(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed.inactive",$content);
    } else {
        file_put_contents(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed",$content);
    }
}

switch($action) {
    case "init":
        $data = array();
        if(file_exists(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed.inactive")) {
            $data["status"] = "open";
            $data["content"] = Strings::file_get_contents_utf8(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed.inactive");
        } else {
            $data["status"] = "closed";
            $data["content"] = Strings::file_get_contents_utf8(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed");
        }
        echo json_encode($data);
        break;
    case "close":
        rename(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed.inactive",AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed");
        echo json_encode(array());
        break;
    case "open":
        rename(AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed",AppConfig::RELATIVE_PATH_MAIN_SERVICES."/conf/closed.inactive");
        echo json_encode(array());
        break;
    case "save":
        echo json_encode(array());
        break;
    case "distribute":
        echo "<pre>";
        echo "Distributing";
        if(!file_exists("../../../../../kopierFraBeta.sh")) {
            die("Script not found");
        }
        system("../../../../../kopierFraBeta.sh");
        echo "</pre>";
        echo "<p id=\"focus\"><strong>Complete</strong></p>";
        break;
}


?>