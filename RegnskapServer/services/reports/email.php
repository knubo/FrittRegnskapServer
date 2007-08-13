<?php


/*
 * Created on Aug 9, 2007
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/reporting/emailer.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$query = array_key_exists("query", $_REQUEST) ? $_REQUEST["query"] : "test";
$subject = array_key_exists("subject", $_REQUEST) ? $_REQUEST["subject"] : "";
$email = array_key_exists("email", $_REQUEST) ? $_REQUEST["email"] : "";
$body = array_key_exists("body", $_REQUEST) ? $_REQUEST["body"] : "";

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

$standard = new AccountStandard($db);
$year = $standard->getOneValue("STD_YEAR");

switch ($action) {
	case "list" :
		$ret = array ();
		switch ($query) {
			case "members" :
				$accYearMem = new AccountYearMembership($db);
				$users = $accYearMem->getReportUsersFull($year);
				break;
			case "newsletter" :
				$accPerson = new AccountPerson($db);
				$accPerson->setNewsletter(1);
				$users = $accPerson->search(false);
				break;
			case "test" :
                if(!$currentUser) {
                	die("Need current user");
                }
                $accPerson = new AccountPerson($db);
                $accPerson->setUser($currentUser);            
				$users = $accPerson->search(false);
				}

		foreach ($users as $one) {
			if (!array_key_exists("email", $one) || !$one["email"]) {
				continue;
			}
			$u = array ();
			$u["name"] = $one["lastname"] . ", " . $one["firstname"];
			$u["email"] = $one["email"];
			$ret[] = $u;
		}

		echo json_encode($ret);
		break;
	case "email" :
		$emailer = new Emailer($db);
		$res = array ();
		$status = $emailer->sendEmail($subject, $email, $body, $standard->getOneValue("STD_EMAIL_SENDER"));
		$res["status"] = $status ? 1 : 0;
		echo json_encode($res);
		break;
	default :
		die("Unknown action $action.");
}
?>
