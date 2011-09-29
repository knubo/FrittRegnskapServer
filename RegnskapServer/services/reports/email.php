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
include_once ("../../classes/reporting/email_content_class.php");
include_once ("../../classes/reporting/email_archive.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/auth/User.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$query = array_key_exists("query", $_REQUEST) ? $_REQUEST["query"] : "all";
$subject = array_key_exists("subject", $_REQUEST) ? $_REQUEST["subject"] : "";
$email = array_key_exists("email", $_REQUEST) ? $_REQUEST["email"] : "";
$body = array_key_exists("body", $_REQUEST) ? $_REQUEST["body"] : "";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$attachments = array_key_exists("attachments", $_REQUEST) ? $_REQUEST["attachments"] : "";
$format = array_key_exists("format", $_REQUEST) ? $_REQUEST["format"] : 0;
$header = array_key_exists("header", $_REQUEST) ? $_REQUEST["header"] : 0;
$footer = array_key_exists("footer", $_REQUEST) ? $_REQUEST["footer"] : 0;
$personid = array_key_exists("personid", $_REQUEST) ? $_REQUEST["personid"] : 0;
$emailSettings = array_key_exists("emailSettings", $_REQUEST) ? $_REQUEST["emailSettings"] : 0;
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

$_REQUEST["username"] = $currentUser;

$standard = new AccountStandard($db);

switch ($action) {
    case "list" :
        $accUser = new User($db);

        if ($emailSettings) {
            $accUser->mergeProfile($currentUser, $emailSettings);
        }

        $ret = array();
        switch ($query) {
            case "members" :
            case "simulate" :
                if (!$year) {
                    $year = $standard->getOneValue(AccountStandard :: CONST_YEAR);
                }
                $accYearMem = new AccountYearMembership($db);
                $users = $accYearMem->getReportUsersFull($year);
                break;
            case "newsletter" :
                $accPerson = new AccountPerson($db, $regnSession->getSuperDBPrefix());
                $accPerson->setNewsletter(1);
                $users = $accPerson->search(false);
                break;
            case "all" :
                $accPerson = new AccountPerson($db, $regnSession->getSuperDBPrefix());
                $users = $accPerson->allWithEmail();
                echo json_encode($users);
                die("");
                break;
            case "test" :
                if (!$currentUser) {
                    die("Need current user");
                }
                $accPerson = new AccountPerson($db, $regnSession->getSuperDBPrefix());
                $accPerson->setUser($currentUser);
                $users = $accPerson->search(false);
        }

        foreach ($users as $one) {
            if (!array_key_exists("email", $one) || !$one["email"]) {
                continue;
            }
            $u = array();
            $u["name"] = $one["lastname"] . ", " . $one["firstname"];
            $u["email"] = $one["email"];
            $u["id"] = $one["id"];
            $ret[] = $u;
        }

        echo json_encode($ret);
        break;

    case "preview":
        $secret = "preview";
        $body = urldecode($body);

        $emailContent = new EmailContent($db);
        $body = $emailContent->attachFooterHeader($body, $footer, $header);

        $body = $emailContent->fillInUnsubscribeURL($body, $secret, $personid);
        $body = $emailContent->replaceCommonVariables($body);

        $html = 0;
        if ($format == "HTML") {
            $html = $body;
            $body = $emailContent->makePlainText($html);
        } else if ($format == "WIKI") {
            $html = $emailContent->makeHTMLFromWiki($body);
            $body = $emailContent->makePlainText($html);
        }

        if($html) {
            echo $html;
        } else {
            echo "<pre>$body</pre>";
        }
        break;
    case "email" :
    case "simulatemail" :
        $regnSession->checkReducedWriteAccess();
        $emailer = new Emailer();
        $res = array();

        $subject = urldecode($subject);
        $body = urldecode($body);
        $attachments = urldecode($attachments);
        $attObjs = $attachments ? json_decode($attachments) : null;
        $sender = $standard->getOneValue(AccountStandard :: CONST_EMAIL_SENDER);

        $accPerson = new AccountPerson($db, $regnSession->getSuperDBPrefix());

        $secret = $accPerson->getSecret($personid);

        if ($action == "email") {
            $prefix = "";
            if (AppConfig :: USE_QUOTA) {
                $prefix = $regnSession->getPrefix() . "/";
            }

            $emailContent = new EmailContent($db);
            $body = $emailContent->attachFooterHeader($body, $footer, $header);

            $body = $emailContent->fillInUnsubscribeURL($body, $secret, $personid);
            $body = $emailContent->replaceCommonVariables($body);

            $html = 0;
            if ($format == "HTML") {
                $html = $body;
                $body = $emailContent->makePlainText($html);
            } else
                if ($format == "WIKI") {
                    $html = $emailContent->makeHTMLFromWiki($body);
                    $body = $emailContent->makePlainText($html);
                }

            $status = $emailer->sendEmail($subject, $email, $body, $sender, $attObjs, $prefix, $html);
        } else {
            $status = true;
        }

        $res["status"] = $status ? 1 : 0;
        echo json_encode($res);
        break;

    case "archive_list":
        $archive = new EmailArchive($db);

        echo json_encode($archive->listAll());
        break;
    case "archive_get":
        $archive = new EmailArchive($db);

        echo json_encode($archive->getOne($id));
        break;
    case "archive_save":
        $archive = new EmailArchive($db);
        echo json_encode(array("insert_id" => $archive->saveOrUpdate($_REQUEST, $regnSession->getArchiveMax())));
        break;

    case "archive_delete":
        $archive = new EmailArchive($db);
        $archive->delete($id);
        echo json_encode(array("result" => 1));
        break;

    default :
        die("Unknown action $action.");
}
?>
