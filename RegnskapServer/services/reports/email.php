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
$query = array_key_exists("query", $_REQUEST) ? $_REQUEST["query"] : "all";
$subject = array_key_exists("subject", $_REQUEST) ? $_REQUEST["subject"] : "";
$email = array_key_exists("email", $_REQUEST) ? $_REQUEST["email"] : "";
$body = array_key_exists("body", $_REQUEST) ? $_REQUEST["body"] : "";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$attachments = array_key_exists("attachments", $_REQUEST) ? $_REQUEST["attachments"] : "";

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

$standard = new AccountStandard($db);
if(!$year) {
    $year = $standard->getOneValue(AccountStandard::CONST_YEAR);
}

switch ($action) {
	case "list" :
		$ret = array ();
		switch ($query) {
			case "members" :
            case "simulate":
				$accYearMem = new AccountYearMembership($db);
				$users = $accYearMem->getReportUsersFull($year);
				break;
			case "newsletter" :
				$accPerson = new AccountPerson($db);
				$accPerson->setNewsletter(1);
				$users = $accPerson->search(false);
				break;
            case "all" :
                $accPerson = new AccountPerson($db);
                $users = $accPerson->allWithEmail();
                echo json_encode($users);
                die("");
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
    case "simulatemail":
        $regnSession->checkReducedWriteAccess();
		$emailer = new Emailer($db);
		$res = array ();

        $subject = urldecode($subject);
        $body = urldecode($body);
        $attachments = urldecode($attachments);
        $attObjs = $attachments ? json_decode($attachments) : null;
        $sender = $standard->getOneValue(AccountStandard::CONST_EMAIL_SENDER);

        if($action == "email") {
		  $status = $emailer->sendEmail($subject, $email, $body, $sender, $attObjs);
        } else {
        	$status = true;
        }

		$res["status"] = $status ? 1 : 0;
		echo json_encode($res);
		break;
	default :
		die("Unknown action $action.");
}
?>
