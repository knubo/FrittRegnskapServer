<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/admin/installer.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : "";
$secret = array_key_exists("secret", $_REQUEST) ? $_REQUEST["secret"] : "";
$hostprefix = array_key_exists("hostprefix", $_REQUEST) ? $_REQUEST["hostprefix"] : "";
$beta = array_key_exists("beta", $_REQUEST) ? $_REQUEST["beta"] : "";
$quota = array_key_exists("quota", $_REQUEST) ? $_REQUEST["quota"] : "";
$description = array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : "";
$wikilogin = array_key_exists("wikilogin", $_REQUEST) ? $_REQUEST["wikilogin"] : "";
$domain = array_key_exists("domain", $_REQUEST) ? $_REQUEST["domain"] : "";
$portal_status = array_key_exists("portal_status", $_REQUEST) ? $_REQUEST["portal_status"] : "";
$portal_title = array_key_exists("portal_title", $_REQUEST) ? $_REQUEST["portal_title"] : "";
$archive_limit = array_key_exists("archive_limit", $_REQUEST) ? $_REQUEST["archive_limit"] : "";
$reduced_mode = array_key_exists("reduced_mode", $_REQUEST) ? $_REQUEST["reduced_mode"] : "";
$parentdbprefix = array_key_exists("parentdbprefix", $_REQUEST) ? $_REQUEST["parentdbprefix"] : "";
$parenthostprefix = array_key_exists("parenthostprefix", $_REQUEST) ? $_REQUEST["parenthostprefix"] : "";

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

if ($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:" . $regnSession->getPrefix());
}

$master = new Master($db);

switch ($action) {
    case "install_details":
        $data = $master->getInstallDetails($id);
        echo json_encode($data);
        break;
    case "update_info":
        $master->updateInstallDetail($id, json_decode($_REQUEST["data"]));
        echo json_encode(array("status"=> 1));
        break;
    case "adminlogin":
        $secret = $master->updateSecret($id);
        $one = $master->getOneInstallation($id);

        $srv = $_SERVER['SERVER_NAME'];
        $parts = explode(".", $srv);

        array_shift($parts);

        $domain = $one["hostprefix"] . "." . implode(".", $parts);

        echo json_encode(array("secret" => $secret, "domain" => $domain));
        break;
    case "get":
        $dbinfo = AppConfig::db(DB::dbhash($one["hostprefix"]));
        $one = $master->getOneInstallation($id);
        $one["db"] = $dbinfo[3];
        echo json_encode($one);
        break;
    case "save":
        $res = $master->updateInstall($id, $hostprefix, $beta, $quota, $description, $wikilogin, $portal_status, $portal_title, $archive_limit, $parentdbprefix, $reduced_mode, $parenthostprefix);
        echo json_encode(array("result" => $res));
        break;
    case "list":
        $installs = $master->getAllInstallations();

        foreach ($installs as &$one) {
            $dbinfo = AppConfig::db(DB::dbhash($one["hostprefix"]));
            $one["db"] = $dbinfo[3];
            $one["secret"] = "?";
        }

        echo json_encode($installs);
        break;

    case "deletePeopleAndAccountingrequest":
        $master->deletePeopleAndAccountingRequest($id);
        echo json_encode(array("status" => "ok"));
        break;

    case "deletePeoplerequest":
        $master->deletePeopleRequest($id);
        echo json_encode(array("status" => "ok"));
        break;

    case "deleteAccountingrequest":
        $master->deleteAccountingRequest($id);
        echo json_encode(array("status" => "ok"));
        break;

    case "deleterequest":
        $master->deleteRequest($id);
        echo json_encode(array("status" => "ok"));
        break;

    case "deletePeople":
        $master->deleteForm($id, $secret, "people", "doDeletePeople");
        break;
    case "deleteAccounting":
        $master->deleteForm($id, $secret, "accounting", "doDeleteAccounting");
        break;
    case "deletePeopleAndAccounting":
        $master->deleteForm($id, $secret, "people and accounting", "doDeletePeopleAndAccounting");
        break;

    case "delete":
        $master->deleteForm($id, $secret, "everything", "doDeleteEverything");
        break;

    case "doDeleteEverything":
        $master->doDelete($id, $secret);
        break;

    case "doDeleteAccounting":
        $master->doDeleteAccounting($id, $secret);
        break;
    case "doDeletePeople":
        $master->doDeletePeople($id, $secret);
        break;
    case "doDeletePeopleAndAccounting":
        $master->doDeletePeopleAndAccounting($id, $secret);
        break;

    case "sendWelcomeLetter":
        $master->sendWelcomeLetter($id);
        break;
    case "sendPortalLetter":
        $master->sendPortalLetter($id);
        break;

    case "installprep":
        $secret = $master->prepareAndAddSecret($wikilogin, $domain);
        echo json_encode(array("secret" => $secret));
        break;
    default:
        die("Unknown action $action");
}


?>