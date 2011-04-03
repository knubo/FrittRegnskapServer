<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";


$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

switch($action) {
    case "status":
        $accStd = new AccountStandard($db);

        $res = $accStd->getOneValue(AccountStandard::CONST_INTEGRATION_SECRET);

        echo json_encode(array("secret" => $res));
        break;

    case "enable":
        $regnSession->checkWriteAccess();

        $accStd = new AccountStandard($db);

        $secret = Strings::createSecret(80);
        $accStd->setValue(AccountStandard::CONST_INTEGRATION_SECRET, $secret);
        echo json_encode(array("secret" => $secret));
        break;

    case "disable":
        $regnSession->checkWriteAccess();
        $accStd = new AccountStandard($db);

        $secret = Strings::createSecret(80);
        $res = $accStd->setValue(AccountStandard::CONST_INTEGRATION_SECRET, "");
        echo json_encode(array("secret" => ""));
        
        break;
}

$regnSession->checkWriteAccess();

?>