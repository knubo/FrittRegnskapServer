<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/events/accountevent.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";

$db = new DB();
$regnSession = new RegnSession($db, 0, "portal");
$username = $regnSession->auth();

$accEvent = new AccountEvent($db);

header('Content-Type: application/json');

switch ($action) {
    case "list":
        echo json_encode($accEvent->listAllActive());
        break;
    case "get":
        $event = $accEvent->getIfActive($_REQUEST["id"]);
        echo $event;
        break;

    case "register":
        $personId = $regnSession->getPersonId();

        $status = $accEvent->register($personId, json_decode($_REQUEST["data"]));
        echo json_encode(array("status" => $status));
        break;
}


?>