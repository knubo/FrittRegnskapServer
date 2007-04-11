<?php
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountposttype.php");

$month = $_GET["month"]; 
$year = $_GET["year"];

$db = new DB();

if (!$month) {
	$standard = new AccountStandard($db);
	$year = $standard->getOneValue("STD_YEAR");
	$month = $standard->getOneValue("STD_MONTH");
}

$accLines = new AccountLine($db);

$monthsLine = $accLines->getMonth($year, $month);

$result = array (
	"year" => $year,
	"month" => $month,
	"lines" => $monthsLine
);

echo json_encode($result);
?>

