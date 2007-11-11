<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/accountsemester.php");
$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";

$accPrice = new AccountMemberPrice($db);

switch ($action) {
    case "all" :
        $accSemester = new AccountSemester($db);

        $all = array();
        $all["price"] = $accPrice->getAll();
        $all["semesters"] = $accSemester->getAll();

        echo json_encode($all);
        break;
    case "save" :
        $regnSession->checkWriteAccess();

//        if (!$project) {
//            echo json_encode($accProj);
//        } else {
//            echo $db->affected_rows();
//        }
        break;
}
?>
