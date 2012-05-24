<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountinvoice.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "keepalive";

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

$accInvoice = new AccountInvoice($db);

switch($action) {
    case "keepalive":
        echo json_encode(array("status" => 1));
        break;
    case "all":
        echo json_encode($accInvoice->getAll());
        break;
    case "save":
        $result = array();
        $result["result"] = $accInvoice->save($_REQUEST);

        echo json_encode($result);
        break;
    case "emailtemplate":
        echo json_encode($accInvoice->getEmailTemplate($_REQUEST["id"]));
        break;
    case "saveEmailTemplate":
        echo json_encode($accInvoice->saveEmailTemplate(json_decode($_REQUEST["emailTemplate"])));
        break;

}
?>