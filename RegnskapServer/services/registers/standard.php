<?php


/*
 * Created on Apr 30, 2007
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "0";
$month = array_key_exists("month", $_REQUEST) ? $_REQUEST["month"] : "0";
$semester = array_key_exists("semester", $_REQUEST) ? $_REQUEST["semester"] : "0";
$email_sender = array_key_exists("email_sender", $_REQUEST) ? $_REQUEST["email_sender"] : "0";
$massletter_due_date = array_key_exists("massletter_due_date", $_REQUEST) ? $_REQUEST["massletter_due_date"] : "0";
$year_post = array_key_exists("year_post", $_REQUEST) ? $_REQUEST["year_post"] : "0";
$course_post = array_key_exists("course_post", $_REQUEST) ? $_REQUEST["course_post"] : "0";
$train_post = array_key_exists("train_post", $_REQUEST) ? $_REQUEST["train_post"] : "0";
$youth_post = array_key_exists("youth_post", $_REQUEST) ? $_REQUEST["youth_post"] : "0";
$end_month_post = array_key_exists("end_month_post", $_REQUEST) ? $_REQUEST["end_month_post"] : "0";
$end_year_post = array_key_exists("end_year_post", $_REQUEST) ? $_REQUEST["end_year_post"] : "0";
$fordringer_posts = array_key_exists("fordringer_posts", $_REQUEST) ? $_REQUEST["fordringer_posts"] : "0";
$register_membership_posts = array_key_exists("register_membership_posts", $_REQUEST) ? $_REQUEST["register_membership_posts"] : "0";
$end_month_transfer_posts = array_key_exists("end_month_transfer_posts", $_REQUEST) ? $_REQUEST["end_month_transfer_posts"] : "0"; 

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accStd = new AccountStandard($db);

switch ($action) {
    case "get" :
        $res = array ();
        $res["year"] = $accStd->getOneValue(AccountStandard::CONST_YEAR);
        $res["month"] = $accStd->getOneValue(AccountStandard::CONST_MONTH);
        $res["semester"] = $accStd->getOneValue(AccountStandard::CONST_SEMESTER);
        $res["email_sender"] = $accStd->getOneValue(AccountStandard::CONST_EMAIL_SENDER);
        $res["massletter_due_date"] = $accStd->getOneValue(AccountStandard::CONST_MASSLETTER_DUE_DATE);

        $res["year_post"] = $accStd->getOneValue(AccountStandard::CONST_BUDGET_YEAR_POST);
        $res["course_post"] = $accStd->getOneValue(AccountStandard::CONST_BUDGET_COURSE_POST);
        $res["train_post"] = $accStd->getOneValue(AccountStandard::CONST_BUDGET_TRAIN_POST);
        $res["youth_post"] = $accStd->getOneValue(AccountStandard::CONST_BUDGET_YOUTH_POST);
        $res["end_month_post"] = $accStd->getOneValue(AccountStandard::CONST_END_MONTH_POST);
        $res["end_year_post"] = $accStd->getOneValue(AccountStandard::CONST_END_YEAR_POST);
        $res["fordringer_posts"] = $accStd->getOneValue(AccountStandard::CONST_FORDRINGER_POSTS);
        $res["register_membership_posts"] = $accStd->getOneValue(AccountStandard::CONST_REGISTER_MEMBERSHIP_POSTS);
        $res["end_month_transfer_posts"] = $accStd->getOneValue(AccountStandard::CONST_END_MONTH_TRANSFER_POSTS);

        $accPrices = new AccountMemberPrice($db);
        $prices = $accPrices->getCurrentPrices();
        $res["cost_membership"] = $prices["year"];
        $res["cost_course"] = $prices["course"];
        $res["cost_practice"] = $prices["train"];

        echo json_encode($res);
        break;
    case "save" :
        $regnSession->checkWriteAccess();
        $res = 0;
        $res = $res | $accStd->setValue(AccountStandard::CONST_YEAR, $year);
        $res = $res | $accStd->setValue(AccountStandard::CONST_MONTH, $month);
        $res = $res | $accStd->setValue(AccountStandard::CONST_SEMESTER, $semester);
        $res = $res | $accStd->setValue(AccountStandard::CONST_EMAIL_SENDER, $email_sender);
        $res = $res | $accStd->setValue(AccountStandard::CONST_MASSLETTER_DUE_DATE, $massletter_due_date);
        $res = $res | $accStd->setValue(AccountStandard::CONST_BUDGET_YEAR_POST, $year_post);
        $res = $res | $accStd->setValue(AccountStandard::CONST_BUDGET_COURSE_POST, $course_post);
        $res = $res | $accStd->setValue(AccountStandard::CONST_BUDGET_TRAIN_POST, $train_post);
        $res = $res | $accStd->setValue(AccountStandard::CONST_BUDGET_YOUTH_POST, $youth_post);
        $res = $res | $accStd->setValue(AccountStandard::CONST_END_MONTH_POST, $end_month_post);
        $res = $res | $accStd->setValue(AccountStandard::CONST_END_YEAR_POST, $end_year_post);
        $res = $res | $accStd->setValue(AccountStandard::CONST_FORDRINGER_POSTS, $fordringer_posts);
        $res = $res | $accStd->setValue(AccountStandard::CONST_REGISTER_MEMBERSHIP_POSTS, $register_membership_posts);
        $res = $res | $accStd->setValue(AccountStandard::CONST_END_MONTH_TRANSFER_POSTS, $end_month_transfer_posts);
        $report = array();
        $report["result"] = $res ? 1 : 0;

        echo json_encode($report);
        break;
}
?>
