<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
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

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "tables";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$personId = $regnSession->getPersonId();

$accKid = new AccountKid($db);
$accPrice = new AccountMemberPrice($db);

switch($action) {
    case "unhandled":
        $result = array();
        $result["data"] = $accKid->unhandled();
        $result["price"] = $accPrice->getCurrentPrices();
        
        echo json_encode($result);
        break;
}

?>