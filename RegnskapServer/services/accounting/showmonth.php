<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/accounting/accountcolumn.php");

if(array_key_exists("month", $_GET)) {
   $month = $_GET["month"]; 
} else {
	$month = 0;
}
if(array_key_exists("year", $_GET)) { 
   $year = $_GET["year"];
} else {
	$year = 0;
}
$db = new DB();

if (!$month || !$year) {
	$standard = new AccountStandard($db);
	$year = $standard->getOneValue("STD_YEAR");
	$month = $standard->getOneValue("STD_MONTH");
}

$accLines = new AccountLine($db);

$monthsLine = $accLines->getMonth($year, $month, 0,0, 1);

$result = array (
	"year" => $year,
	"month" => $month,
	"lines" => $monthsLine,
);

echo json_encode($result);

?>

