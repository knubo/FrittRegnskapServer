<?php

/*
 * Created on Apr 13, 2007
 * 
 * query/insert/update service for accountline.
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "query";
$line = array_key_exists("line", $_REQUEST) ? $_REQUEST["line"] : 1;
$desc = array_key_exists("desc", $_REQUEST) ? $_REQUEST["desc"] : 0;
$day = array_key_exists("day", $_REQUEST) ? $_REQUEST["day"] : 0;
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$month = array_key_exists("month", $_REQUEST) ? $_REQUEST["month"] : 0;
$attachment = array_key_exists("attachment", $_REQUEST) ? $_REQUEST["attachment"] : 0;
$postnmb = array_key_exists("postnmb", $_REQUEST) ? $_REQUEST["postnmb"] : 0;
$navigate = array_key_exists("navigate", $_REQUEST) ? $_REQUEST["navigate"] : 0;

$db = new DB();

$regnSession = new RegnSession($db);
$regnSession->auth();

$accLine = new AccountLine($db);

if ($day && $month && $year) {
	$occured = new eZDate($year, $month, $day);
}
switch ($action) {
	case "query" :
        if($navigate) {
        	$line = $accLine->findLine($navigate, $line);
        }
		$accLine->read($line);
		$accLine->fetchAllPosts();
		echo json_encode($accLine);
		break;
	case "insert" :
        $regnSession->checkWriteAccess();
    
		if (!$postnmb || !$day || !$desc || !$occured || !$line || !$attachment) {
			die("Missing params for insert of accountline.");
		}
		$insertAC = new AccountLine($db, $postnmb, $attachment, $desc, $day, $line, $occured);
		$insertAC->store();
		echo $insertAC->getId();
		break;

	case "update" :
        $regnSession->checkWriteAccess();
		if (!$postnmb || !$day ||  !$desc || !$line || !$occured || !$attachment) {
			die("Missing params for update of accountline.");
		}
		$updateAcc = new AccountLine($db, $postnmb, $attachment, $desc, $day, $line, $occured);
		
		echo $updateAcc->update();
		break;
	default:
		die("Missing action");
}
?>

