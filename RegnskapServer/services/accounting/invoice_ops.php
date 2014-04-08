<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/odf/odf.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/accounting/helpers/Luhn.php");
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
        $regnSession->checkWriteAccess();
        $result = array();
        $result["result"] = $accInvoice->save($_REQUEST);

        echo json_encode($result);
        break;
    case "emailtemplate":
        echo json_encode($accInvoice->getEmailTemplate($_REQUEST["id"]));
        break;
    case "saveEmailTemplate":
        $regnSession->checkWriteAccess();
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

    case "invoices_not_sent":
        echo json_encode($accInvoice->invoicesNotSent());
        break;

    case "create_invoices":
        $regnSession->checkWriteAccess();

        $userId = $regnSession->getPersonId();
        $accInvoice->create_invoices($userId, json_decode($_REQUEST["invoices"]),
            json_decode($_REQUEST["receivers"]), $_REQUEST["invoice_type"]);

        echo json_encode(array("status" => 1));
        break;


    case "invoice":
        echo json_encode($accInvoice->invoice($_REQUEST["receiver_id"]));
        break;

    case "invoices":
        echo json_encode($accInvoice->invoices($_REQUEST["invoice"], $_REQUEST["due_date"]));
        break;

    case "invoice_paid":
        $regnSession->checkWriteAccess();

        $status = $accInvoice->invoicePaidInTransaction($_REQUEST["invoice_recepiant "], $_REQUEST["paid_day"], $_REQUEST["amount"], $_REQUEST["debet_post"]);

        echo json_encode(array("status" => $status));
        break;
    case "search":
        $res = $accInvoice->search($_REQUEST);
        echo json_encode($res);
        break;
    case "change_invoice_status":
        $res = $accInvoice->changeInvoiceStatus($_REQUEST["receiver_id"], $_REQUEST["status"]);
        echo json_encode(array("status" => $res ? 1 : 0));
        break;

    case "test_luhn":
        echo "15-".Luhn::generateDigit("15");
        break;
    case "invoice_paper":
        $ids = json_decode($_REQUEST["invoices"]);
        $invoice_template = $_REQUEST["invoice_template"];
        $possibleInvoices = $accInvoice->invoicesForODF($invoice_template);


        $prefix = "";
        if (AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix() . "/";
        }

        $openfile = "../../storage/" . $prefix . "/" . $invoice_template;


        if (substr($openfile, -4) != ".odt") {
            die("Not an open office file");
        }

        $odf = new odf($openfile);

        $article = $odf->setSegment('page');

        $date = new eZDate();
        $charset = 'UTF-8';

        $db->begin();

        foreach ($possibleInvoices as $one) {
            if (in_array($one["id"], $ids)) {
                $article->setVarsSilent("firstname", $one["firstname"], 0, $charset);
                $article->setVarsSilent("lastname", $one["lastname"], 0, $charset);
                $article->setVarsSilent("address", $one["address"], 0, $charset);
                $article->setVarsSilent("city", $one["city"], 0, $charset);
                $article->setVarsSilent("zipcode", $one["postnmb"], 0, $charset);

                $invoice = $one["id"] . "-" . Luhn::generateDigit($one["id"]);
                $article->setVarsSilent("invoice", $invoice, 0, $charset);
                $article->setVarsSilent("amount", $one["amount"], 0, $charset);
                $article->merge();

                $accInvoice->changeInvoiceStatus($one["id"], 2);
            }
        }

        $db->commit();

        $odf->mergeSegment($article);

        // We export the file
        $odf->exportAsAttachedFile();


}
?>