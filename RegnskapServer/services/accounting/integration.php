<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";
$email = array_key_exists("email", $_REQUEST) ? $_REQUEST["email"] : "";


$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

switch($action) {
    case "status":
        $accStd = new AccountStandard($db);

        $vals = $accStd->getValues(array(AccountStandard::CONST_INTEGRATION_SECRET, AccountStandard::CONST_INTEGRATION_EMAIL));

        echo json_encode(array("secret" => $vals[AccountStandard::CONST_INTEGRATION_SECRET],
        					   "email"  => $vals[AccountStandard::CONST_INTEGRATION_EMAIL]));
        break;

    case "update":
        $regnSession->checkWriteAccess();

        $accStd = new AccountStandard($db);

        $secret = $accStd->getOneValue(AccountStandard::CONST_INTEGRATION_SECRET);
        $accStd->setValue(AccountStandard::CONST_INTEGRATION_EMAIL, $email);

        echo json_encode(array("secret" => $secret, "email" => $email));
        break;

    case "enable":
        $regnSession->checkWriteAccess();

        $accStd = new AccountStandard($db);

        $secret = Strings::createSecret(80);
        $accStd->setValue(AccountStandard::CONST_INTEGRATION_SECRET, $secret);
        $accStd->setValue(AccountStandard::CONST_INTEGRATION_EMAIL, $email);
        echo json_encode(array("secret" => $secret, "email" => $email));
        break;

    case "disable":
        $regnSession->checkWriteAccess();
        $accStd = new AccountStandard($db);

        $secret = Strings::createSecret(80);
        $res = $accStd->setValue(AccountStandard::CONST_INTEGRATION_SECRET, "");
        echo json_encode(array("secret" => "", "email" => $email));

        break;
}

$regnSession->checkWriteAccess();

?>