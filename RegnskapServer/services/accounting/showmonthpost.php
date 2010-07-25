<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/accounting/accountcolumn.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$month = array_key_exists("month", $_REQUEST) ? $_GET["month"] : 0;
$year = array_key_exists("year", $_REQUEST) ? $_GET["year"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


if (!$month || !$year) {
	$standard = new AccountStandard($db);
	$values = $standard->getValues(array(AccountStandard::CONST_YEAR, AccountStandard::CONST_MONTH));
	$year = $values[AccountStandard::CONST_YEAR];
	$month = $values[AccountStandard::CONST_MONTH];
}

$accLines = new AccountLine($db);

$data = $accLines->getMonth($year, $month, 0, 0, 1);

$monthsLine = $data["lines"];


foreach($monthsLine as $one) {
    if(is_object($one)) {
    	$one->fetchAllPosts();
    }
}

echo json_encode($monthsLine);

?>