<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/auth/RegnSession.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";


$accSemester = new AccountSemester($db);

switch ($action) {
    case "all" :
        $all = $accSemester->getAll();
        echo json_encode($all);
        break;
}

?>
