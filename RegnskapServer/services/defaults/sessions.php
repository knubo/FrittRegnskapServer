<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/auth/User.php");

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

$sessions = $regnSession->allSessions();

$result = array();

foreach($sessions as $one) {
    $data = array();

    $datavalue = $one["DataValue"];
    $data["LastUpdate"] = $one["LastUpdated"];

    $pairs = explode(";", $datavalue);

    foreach($pairs as $one) {
        if(strncmp($one, "username", 8) == 0 ) {
            $data["username"] = substr($one, stripos($one, "\"")+1, -1);
        }

        if(strncmp($one, "ip", 2) == 0) {
            $data["ip"] = substr($one, stripos($one, "\"")+1, -1);
        }

    }
    
    if(!$data["ip"]) {
        $data["ip"] = "?";
    }
    
    $result[] = $data;
}


echo json_encode($result);
?>

