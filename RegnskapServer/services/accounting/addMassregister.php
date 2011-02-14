<?php
/*
 * Created on May 23, 2007
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountcount.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");


$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "";
$day = array_key_exists("day", $_REQUEST) ? $_REQUEST["day"] : 0;
$desc = array_key_exists("desc", $_REQUEST) ? $_REQUEST["desc"] : 0;
$attachment = array_key_exists("attachment", $_REQUEST) ? $_REQUEST["attachment"] : 0;
$postnmb = array_key_exists("postnmb", $_REQUEST) ? $_REQUEST["postnmb"] : 0;
$amount = array_key_exists("amount", $_REQUEST) ? $_REQUEST["amount"] : 0;
$debet = array_key_exists("debet", $_REQUEST) ? $_REQUEST["debet"] : 0;
$kredit = array_key_exists("kredit", $_REQUEST) ? $_REQUEST["kredit"] : 0;
$project = array_key_exists("project", $_REQUEST) ? $_REQUEST["project"] : 0;


if($action != "save") {
    die("Need action");
}

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$regnSession->checkWriteAccess();

$db->begin();

$acStandard = new AccountStandard($db);
$active_month = $acStandard->getOneValue(AccountStandard::CONST_MONTH);
$active_year = $acStandard->getOneValue(AccountStandard::CONST_YEAR);

$accLine = new AccountLine($db);
$accLine->setNewLatest($desc, $day, $active_year, $active_month);
$accLine->setAttachment($attachment);
$accLine->setPostnmb($postnmb);
$accLine->store();

$lineId = $accLine->getId();

$accLine->addPostSingleAmount($lineId, '1', $debet, $amount, $project); 
$accLine->addPostSingleAmount($lineId, '-1', $kredit, $amount, $project); 

$nextAttachment = $accLine->getNextAttachmentNmb($active_year);
$nextPostNmb = $accLine->getNextPostnmb($active_year, $active_month);

$db->commit();

$res = array();
$res["attachment"] = $nextAttachment;
$res["postnmb"] = $nextPostNmb;

echo json_encode($res);

?>
