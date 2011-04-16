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

$ret = $standard->getValues(array(AccountStandard::CONST_YEAR, AccountStandard::CONST_MONTH, AccountStandard::CONST_SEMESTER));

$year = $ret[AccountStandard::CONST_YEAR];
$month = $ret[AccountStandard::CONST_MONTH];
$semester = $ret[AccountStandard::CONST_SEMESTER];

$newLineData = $accLine->getNewLineData($year,$month);

$attachment = $newLineData["attachnmb"];
$postnmb = $newLineData["postnmb"];

$res = array("year"=>$year,
		     "month"=>$month, 
		     "attachment"=> $attachment + 1, 
		     "postnmb"=> $postnmb ? $postnmb+5 : 5,
             "first_time_complete" => $first_time, 
             "semester" => $semester,
             "line" => $newLineData["line"],
             "debet" => $newLineData["debet"],
             "kredit" => $newLineData["kredit"]);

echo json_encode($res)

?>

