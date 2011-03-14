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
include_once ("../../classes/accounting/accounthappening.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

 
 $action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "";
 $day = array_key_exists("day", $_REQUEST) ? $_REQUEST["day"] : 0;
 $desc = array_key_exists("desc", $_REQUEST) ? $_REQUEST["desc"] : 0;
 $attachment = array_key_exists("attachment", $_REQUEST) ? $_REQUEST["attachment"] : 0;
 $postnmb = array_key_exists("postnmb", $_REQUEST) ? $_REQUEST["postnmb"] : 0;
 $amount = array_key_exists("amount", $_REQUEST) ? $_REQUEST["amount"] : 0;
 $post = array_key_exists("post", $_REQUEST) ? $_REQUEST["post"] : 0;
 
 
 
 $postcols = array();
 
 foreach(AppConfig::CountColumns() as $one) {
 	if(array_key_exists($one, $_REQUEST)) {
 		$postcols[$one] = $_REQUEST[$one];
 	}
 }
 
 if($action != "save") {
 	die("Need action");
 }
 
$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$regnSession->checkWriteAccess();
$personId = $regnSession->getPersonId();


$db->begin();

$happening = new AccountHappening($db);
$happening->load($post);


$acStandard = new AccountStandard($db);
$active_month = $acStandard->getOneValue(AccountStandard::CONST_MONTH);
$active_year = $acStandard->getOneValue(AccountStandard::CONST_YEAR);
  
$accLine = new AccountLine($db);
$accLine->setNewLatest($desc, $day, $active_year, $active_month, $personId);
$accLine->setAttachment($attachment);
$accLine->setPostnmb($postnmb);
$accLine->store();

$happeningId = $accLine->getId();

if(count($postcols) > 0) {
	$accCount = new AccountCount($db);
	$accCount->save($happeningId, $postcols);
}

$accLine->addPostSingleAmount($happeningId, 
		      '1', 
		      $happening->getDebetpost(), 
		      $amount);

$accLine->addPostSingleAmount($happeningId, 
		      '-1', 
		      $happening->getKreditpost(), 
		      $amount);


$db->commit();  

$res = array();
$res["id"] = $happeningId;

echo json_encode($res);
 
?>
