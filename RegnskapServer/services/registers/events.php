<?php

/*
 * Created on May 19, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accounthappening.php");
include_once ("../../classes/events/accountevent.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accEvent = new AccountEvent($db);

switch ($action) {
    case "save":
        $id = $accEvent->save($_REQUEST["data"]);

        echo json_encode(array("id" => $id));

        break;
    case "list":
        echo json_encode($accEvent->listAll());

        break;
    case "get":
        echo $accEvent->get($_REQUEST["id"]);
        break;

    case "list_participants":
        echo json_encode($accEvent->listParticipants());
        break;

    case "participants":
        echo json_encode($accEvent->participants($_REQUEST["id"]));
        break;


}

?>