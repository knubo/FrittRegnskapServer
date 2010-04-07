<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/accounting/accountcolumn.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$month = array_key_exists("month", $_REQUEST) ? $_REQUEST["month"] : 0;
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;

$db = new DB();

$regnSession = new RegnSession($db);
$regnSession->auth();


if (!$month || !$year) {
	$standard = new AccountStandard($db);
	$year = $standard->getOneValue(AccountStandard::CONST_YEAR);
	$month = $standard->getOneValue(AccountStandard::CONST_MONTH);
}

$accLines = new AccountLine($db);

$monthsLine = $accLines->getMonth($year, $month, 0, 0, 1);

$result = array (
	"year" => $year,
	"month" => $month,
	"monthinfo" => $monthsLine,
	
);

echo json_encode($result);
?>

