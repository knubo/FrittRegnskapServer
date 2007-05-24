<?php
/*
 * Created on May 23, 2007
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
 
 
 $action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "";
 $day = array_key_exists("day", $_REQUEST) ? $_REQUEST["day"] : 0;
 $desc = array_key_exists("desc", $_REQUEST) ? $_REQUEST["desc"] : 0;
 $attachment = array_key_exists("attachment", $_REQUEST) ? $_REQUEST["attachment"] : 0;
 $postnmb = array_key_exists("ostnmb", $_REQUEST) ? $_REQUEST["postnmb"] : 0;
 $amount = array_key_exists("amount", $_REQUEST) ? $_REQUEST["amount"] : 0;
 $post = array_key_exists("post", $_REQUEST) ? $_REQUEST["post"] : 0;
 
 $postcols = array();
 
 foreach(AppConfig::CountColumns() as $one) {
 	if(array_key_exists($one)) {
 		$postcols[$one] = $_REQUEST[$one];
 	}
 }
 
 if($action != "save") {
 	die("Need action");
 }
 
$db = new DB();
$db->begin();

$happening = new AccountHappening($db);
$happening->load($post);


$acStandard = new AccountStandard($db);
$active_month = $acStandard->getOneValue("STD_MONTH");
$active_year = $acStandard->getOneValue("STD_YEAR");
  
$accLine = new AccountLine($db);
$accLine->setNewLatest("$arrtype", $day, $active_year, $active_month);
$accLine->setAttachment($attachment);
$accLine->setPostnmb($postnmb);
$accLine->store();

$happeningId = $accLine->getId();

$accLine->addPost($happeningId, 
		      '1', 
		      $happening->getDebetpost(), 
		      $amount, 
		      $amountdes);

$accLine->addPost($happeningId, 
		      '-1', 
		      $happening->getKreditpost(), 
		      $amount, 
		      $amountdes);


$db->commit();  
 
 
?>
