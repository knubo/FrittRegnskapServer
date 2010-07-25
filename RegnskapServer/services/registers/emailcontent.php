<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/reporting/email_content_class.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/auth/User.php");



/* Used for admin */
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "setup_init";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : "";
$name = array_key_exists("name", $_REQUEST) ? $_REQUEST["name"] : "";
$text = array_key_exists("text", $_REQUEST) ? $_REQUEST["text"] : "";
$header = array_key_exists("header", $_REQUEST) ? $_REQUEST["header"] : "";

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

switch ($action) {
    case "setup_get":
        $contentHelper = new EmailContent($db);
        echo json_encode($contentHelper->get($id));
        break;
    case "report_init":
        $contentHelper = new EmailContent($db);
        $accUser = new User($db);
        $data = $contentHelper->getAll();
        $data["profile"] = $accUser->getProfile($currentUser);
        
        echo json_encode($data);
        break;
    case "setup_init":
        $contentHelper = new EmailContent($db);
        echo json_encode($contentHelper->getAll());
        break;
    case "setup_save":
        $contentHelper = new EmailContent($db);
        echo json_encode($contentHelper->save($id, $name, $text, $header));
        break;
    default :
        die("Unknown action $action.");

}
?>