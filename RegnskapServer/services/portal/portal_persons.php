<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

//header("Content-Type: application/json");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
$data = array_key_exists("data", $_REQUEST) ? $_REQUEST["data"] : 0;

$db = new DB();
$regnSession = new RegnSession($db,0, "portal");
$username = $regnSession->auth();


switch($action) {
    case "me":
        $personId = $regnSession->getPersonId();
        $accPerson = new AccountPerson($db);
        echo json_encode($accPerson->getOnePortal($personId));
        break;
        
    case "save":
        $personId = $regnSession->getPersonId();
        $accPerson = new AccountPerson($db);
        $accPerson->savePortalUser($personId, json_decode($data));
        echo json_encode(array("result" => "ok"));
        break;
        
}