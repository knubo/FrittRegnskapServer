<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountcount.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountbelonging.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "tables";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$personId = $regnSession->getPersonId();

$accBelonging = new AccountBelonging($db);

switch($action) {

    case "list":
        echo json_encode($accBelonging->listAll($_REQUEST));
        break;
    case "get":
        echo json_encode($accBelonging->getOne($_REQUEST["id"]));
        break;
    case "add":
        $regnSession->checkWriteAccess();

        $res = $accBelonging->addBelonging($_REQUEST, $personId);

        echo json_encode($res);

        break;

    case "delete":
        $regnSession->checkWriteAccess();
        $res = $accBelonging->deleteBeloning($_REQUEST["id"], $_REQUEST["change"], $personId);
        
        echo json_encode($res);
        break;

    case "update":
        $regnSession->checkWriteAccess();

        $res = $accBelonging->updateBelonging($_REQUEST, $personId);

        echo json_encode($res);

        break;
    case "updatePreview":
        $regnSession->checkWriteAccess();
        $res = $accBelonging->updatePreview($_REQUEST);

        if(count($res) == 0) {
            $res = $accBelonging->updateBelonging($_REQUEST, $personId);
        }
        
        echo json_encode($res);

        break;


}



?>