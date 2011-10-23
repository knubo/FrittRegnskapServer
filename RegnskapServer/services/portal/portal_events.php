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

switch ($action) {
    case "list":
        echo json_encode($accEvent->listAllActive());
        break;
}


?>