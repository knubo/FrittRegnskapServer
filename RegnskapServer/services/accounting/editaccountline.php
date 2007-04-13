<?php


/*
 * Created on Apr 13, 2007
 * 
 * query/insert/update service for accountline.
 */

include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");

$action = array_key_exists("action", $_GET) ? $_GET["action"] : "query";
$line = array_key_exists("line", $_GET) ? $_GET["line"] : 1;
$desc = array_key_exists("desc", $_GET) ? $_GET["desc"] : 1;
$day = array_key_exists("day", $_GET) ? $_GET["day"] : 1;
$year = array_key_exists("year", $_GET) ? $_GET["year"] : 1;
$month = array_key_exists("month", $_GET) ? $_GET["month"] : 1;
$attachment = array_key_exists("attachment", $_GET) ? $_GET["attachment"] : 1;
$postnmb = array_key_exists("postnmb", $_GET) ? $_GET["postnmb"] : 1;

$db = new DB();
$accLine = new AccountLine($db);

if($day && $month && $year) {
	$occured = new eZDate($year, $month, $day);
} 
switch ($action) {
	case "query" :
		$accLine->read($line);
		$accLine->fetchAllPosts();
		echo json_encode($accLine);
		break;
	case "insert" :
		$updateAcc = new AccountLine($db, $postnmb, $desc, $day, $line, $occured);
		$accLine->store();
		break;

	case "update" :
		$updateAcc = new AccountLine($db, $postnmb, $desc, $day, $line, $occured);
		$accLine->update();
		break;
}
?>

