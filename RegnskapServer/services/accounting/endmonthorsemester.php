<?php

/*

* Created on May 28, 2007
*
*/
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountbelonging.php");
include_once ("../../classes/accounting/accountkid.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/helpers/endmonthhelper.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$endHelper = new EndMonthHelper($db);

$accountBelonging = new AccountBelonging($db);


switch ($action) {
    case "test":
        $acStandard = new AccountStandard($db);
        $acPostType = new AccountPostType($db);
        $endPostIds = $acStandard->getOneValueAsArray(AccountStandard::CONST_END_MONTH_TRANSFER_POSTS);
        $endPosts = $acPostType->getSomeIndexedById($endPostIds);
        echo json_encode($endPosts);
        break;
    case "status" :
        $acStandard = new AccountStandard($db);
        $belongings = new AccountBelonging($db);
        $accKid = new AccountKID($db);

        $std = $acStandard->getValues(array(AccountStandard::CONST_YEAR, AccountStandard::CONST_MONTH));

        $res = array();
        $res["posts"] = $endHelper->status();
        $res["year"] = $std[AccountStandard::CONST_YEAR];
        $res["month"] = $std[AccountStandard::CONST_MONTH];
        $res["deprecation"] = $belongings->listItemsToDeprecate();
        $res["kids"] =  $accKid->unhandledForMonth($res["year"], $res["month"]);

        echo json_encode($res);
        break;
    case "endsemester" :
        $regnSession->checkWriteAccess();

        $accSemester = new AccountSemester($db);
        $acStandard = new AccountStandard($db);

        $nextSemester = $accSemester->getFallSemester();

        $res = $endHelper->insertParams();

        if ($res["month"] == 12) {
            header("HTTP/1.0 514 Illegal state");

            die("Can't end last month in year - use end year");
        }

        $db->begin();


        if($_REQUEST["deprecate"]) {
            $accountBelonging->addDeprecationLine($res, $regnSession->getPersonId(), $_REQUEST["deprdesc"]);
        }

        $res = $endHelper->endMonth($res, $regnSession->getPersonId());


        $acStandard->setValue(AccountStandard::CONST_SEMESTER, $nextSemester);
        $db->commit();
        echo json_encode("1");
        break;
    case "endmonth" :
        $regnSession->checkWriteAccess();
        $res = $endHelper->insertParams();

        if ($res["month"] == 12) {
            header("HTTP/1.0 514 Illegal state");

            die("Can't end last month in year - use end year");
        }

        $lastSpringMonth = $res["lastSpringMonth"];
        if($lastSpringMonth == 0) {
            $lastSpringMonth = 6;
        }



        if($res["month"] == $lastSpringMonth) {
            header("HTTP/1.0 513 Validation Error");

            die(json_encode(array("last_month")));
        }



        $db->begin();


        if($_REQUEST["deprecate"]) {
            $accountBelonging->addDeprecationLine($res, $regnSession->getPersonId(), $_REQUEST["deprdesc"]);
        }

        $res = $endHelper->endMonth($res, $regnSession->getPersonId());

        $db->commit();
        echo json_encode("1");
        break;
    default:
        echo "Unknown action $action";
        break;

}
?>
