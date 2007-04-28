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

$db = new DB();
$accLine = new AccountLine($db);
$standard = new AccountStandard($db);

$year = $standard->getOneValue("STD_YEAR");
$month = $standard->getOneValue("STD_MONTH");
$attachment = $accLine->getNextAttachmentNmb($year);
$postnmb = $accLine->getNextPostnmb($year, $month);

$res = array("year"=>$year, "month"=>$month, "attachment"=>$attachment, "postnmb"=>$postnmb);

echo json_encode($res)

?>

