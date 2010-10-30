<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";

$db = new DB();
$regnSession = new RegnSession($db,0, "portal");
$username = $regnSession->auth();


switch($action) {
    case "email":
        header("Content-Type: application/json");

        $body = $_REQUEST["body"];
        $receiverId = $_REQUEST["personId"];
        $personId = $regnSession->getPersonId();
        $accPerson = new AccountPerson($db);
        $sendersData = $accPerson->getOne($personId);
        $receiverData = $accPerson->getOne($receiverId);

        $standard = new AccountStandard($db);
        $sender = $standard->getOneValue(AccountStandard :: CONST_EMAIL_SENDER);
        $emailer = new Emailer();

        $allBody = "Denne eposten er sendt til deg via Fritt Regnskaps Medlemsportal. ".$sendersData["firstname"]." ".$sendersData["lastname"]." har sendt deg en melding.\n".
        "Vil du sende et svar, send en epost direkte til hans/hennes epostadresse: ".$sendersData["email"]."\nInnholdet i meldingen er:\n\n".$body;


        $status = $emailer->sendEmail($sendersData["firstname"]." har sendt deg en melding via Fritt Regnskap",$receiverData["email"], $allBody, $sender, 0);
         
        echo json_encode(array("status" => $status));
         
        break;

}