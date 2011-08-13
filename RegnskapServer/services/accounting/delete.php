<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountdelete.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();
$regnSession->checkWriteAccess();

$accPerson = new AccountPerson($db, $regnSession->getSuperDBPrefix());
$accPerson->setUser($currentUser);
$users = $accPerson->search(false);

$data = $_REQUEST;

$to = "admin@frittregnskap.no";


foreach ($one as $users) {
    if (!array_key_exists("email", $one) || !$one["email"]) {
        continue;
    }

    $to .=",".$one["email"];
}

$data["to"] = $to;
$data["from"] = "admin@frittregnskap.no";

$accDelete = new AccountDelete($db);
$accDelete->registerDeleteActionsAndSendEmail($data);

echo json_encode(array("status" => 1));
?>