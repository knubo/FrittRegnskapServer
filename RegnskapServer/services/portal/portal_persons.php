<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");


$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";

$db = new DB();
$regnSession = new RegnSession($db,0, "portal");
$username = $regnSession->auth();

switch($action) {
    case "get":
        
}