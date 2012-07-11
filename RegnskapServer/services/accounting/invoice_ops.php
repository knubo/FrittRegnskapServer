<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountinvoice.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "keepalive";

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

$accInvoice = new AccountInvoice($db);

switch ($action) {
    case "keepalive":
        echo json_encode(array("status" => 1));
        break;
    case "all":
        $accPrices = new AccountMemberPrice($db);
        $accStd = new AccountStandard($db);

        $data = array();
        $data["invoices"] = $accInvoice->getAll();
        $data["prices"] = $accPrices->getCurrentPrices();

        $std = $accStd->getValues(array(AccountStandard::CONST_MONTH, AccountStandard::CONST_YEAR));
        $data["month"] = $std[AccountStandard::CONST_MONTH];
        $data["year"] = $std[AccountStandard::CONST_YEAR];
        echo json_encode($data);
        break;
    case "get":
        echo json_encode($accInvoice->getOne($_REQUEST["id"]));
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

    case "members_must_have_year":
        $accStd = new AccountStandard($db);
        $year = $accStd->getOneValue(AccountStandard::CONST_YEAR);

        $accYear = new AccountyearMembership($db);
        $data = $accYear->missingMemberships($year);

        echo json_encode($data);
        break;
    case "members_must_have_semester":
        $accStd = new AccountStandard($db);
        $semester = $accStd->getOneValue(AccountStandard::CONST_SEMESTER);

        $accSemester = new AccountSemesterMembership($db);
        $data = $accSemester->missingMemberships($semester);

        echo json_encode($data);
        break;
    case "members_previous_year":
        $accStd = new AccountStandard($db);
        $year = $accStd->getOneValue(AccountStandard::CONST_YEAR);

        $accYear = new AccountyearMembership($db);
        $data = $accYear->missingMembershipsComparedToPrevious($year);

        echo json_encode($data);
        break;
    case "members_previous_semester":
        $accStd = new AccountStandard($db);
        $semester = $accStd->getOneValue(AccountStandard::CONST_SEMESTER);

        $accSemester = new AccountSemesterMembership($db);
        $data = $accSemester->missingMembershipsComparedToPrevious($semester);

        echo json_encode($data);
        break;


}
?>