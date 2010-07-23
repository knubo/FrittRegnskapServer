<?php
/*
 * Created on Apr 13, 2007
 *
 * Fetches default values for registering a new regn_line.
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accLine = new AccountLine($db);
$standard = new AccountStandard($db);

$ret = $standard->getValues(array(AccountStandard::CONST_YEAR, AccountStandard::CONST_MONTH, AccountStandard::FIRST_TIME, AccountStandard::CONST_SEMESTER));

$year = $ret[AccountStandard::CONST_YEAR];
$month = $ret[AccountStandard::CONST_MONTH];
$semester = $ret[AccountStandard::CONST_SEMESTER];
$first_time = $ret[AccountStandard::FIRST_TIME];
$attachment = $accLine->getNextAttachmentNmb($year);
$postnmb = $accLine->getNextPostnmb($year, $month);

$res = array("year"=>$year, "month"=>$month, "attachment"=>$attachment, "postnmb"=>$postnmb, "first_time_complete" => $first_time, "semester" => $semester);

echo json_encode($res)

?>

