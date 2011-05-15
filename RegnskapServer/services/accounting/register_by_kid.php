<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/KID.php");
include_once ("../../classes/accounting/accountkid.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/helpers/memberships.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "unhandled";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$personId = $regnSession->getPersonId();

$accKid = new AccountKid($db);

switch ($action) {
    case "unhandled":
        $accPrice = new AccountMemberPrice($db);
        $accStandard = new AccountStandard($db);

        $result = array();
        $result["data"] = $accKid->unhandled();
        $result["price"] = $accPrice->getCurrentPrices();
        $posts = array(AccountStandard::CONST_BUDGET_YEAR_POST,
                       AccountStandard::CONST_BUDGET_TRAIN_POST,
                       AccountStandard::CONST_BUDGET_COURSE_POST,
                       AccountStandard::CONST_BUDGET_YOUTH_POST);
        $result["posts"] = $accStandard->getValues($posts);

        echo json_encode($result);
        break;
    case "register":
        $status = $accKid->register($_REQUEST["data"], $personId);

        echo json_encode(array("status" => $status));
        break;

    case "list":
        $dbm = new DB(0, DB::MASTER_DB);
        $master = new Master($dbm);
        $masterRecord = $master->get_master_record();
        
        echo json_encode($accKid->listKID($masterRecord, $_REQUEST));
        break;
}

?>