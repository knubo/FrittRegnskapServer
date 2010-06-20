<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/admin/SQLS.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : "";
$installid = array_key_exists("installid", $_REQUEST) ? $_REQUEST["installid"] : "";
$sql = array_key_exists("sql", $_REQUEST) ? $_REQUEST["sql"] : "";
$secret = array_key_exists("secret", $_REQUEST) ? $_REQUEST["secret"] : "";

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

if($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:".$regnSession->getPrefix());
}

$sqls = new SQLS($db);

switch($action) {
    case "add":
        $sqls->addSQL($sql);
        echo json_encode(array());
        break;
    case "delete":
        $sqls->delete($id);
        echo json_encode(array());
        break;
    case "betacomplete":
        $sqls->betaComplete($id);
        echo json_encode(array());
        break;
    case "othercomplete":
        $sqls->otherComplete($id);
        echo json_encode(array());
        break;
    case "get":
        $prep = $db->prepare("update installations set sqlIdToRun = ?");
        $prep->bind_params("i", $id);
        $prep->execute();

        $data = array();
        $data["sql"] = $sqls->getOneSQL($id);

        $master = new Master($db);
        $installs = $master->getAllInstallations(" order by beta desc, hostprefix");
        foreach($installs as &$one) {
            $dbinfo = AppConfig::db(DB::dbhash($one["hostprefix"]));
            $one["db"] = $dbinfo[3];
        }
        $data["installs"] = $installs;
        echo json_encode($data);
        break;
    case "verify":
        $sqls->verifyForm($id, $secret);
        break;
    case "list":
        echo json_encode($sqls->getAll());
        break;

    case "confirmVerify":
        $sqls->confirmVerify($id, $secret);
        break;
    case "run":
        $master = new Master($db);

        echo json_encode($sqls->run($id, $master->getOneInstallation($installid)));
        break;

    default:
        die("Did not get leagal action $action");



}