<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/validators/emailvalidator.php");
include_once ("../../classes/validators/validatorstatus.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/auth/User.php");


$user = $_REQUEST["user"];
$password = $_REQUEST["password"];
$action = $_REQUEST["action"];
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "changes";
$date = array_key_exists("date", $_REQUEST) ? trim($_REQUEST["date"]) : "";


$db = new DB(0, DB::MASTER_DB);
$master = new Master($db);
$masterRecord = $master->get_master_record();

if(!$masterRecord) {
    $arr = array (
				'error' => 'Ikke identifisert database.'
				);
				echo json_encode($arr);
				break;
}
$dbu = new DB();

$auth = new User($dbu);

if (!$auth->authenticate($user, $password, $masterRecord["dbprefix"]) == User :: AUTH_OK) {
    die("Authentication failed");
}

$sess = new RegnSession($dbu, $masterRecord["dbprefix"]);

if(!session_start()) {
    die("Failed to start session");
}

$_SESSION["prefix"] = $masterRecord["dbprefix"];
$_SESSION["diskquota"] = $masterRecord["diskquota"];
$_SESSION["username"] = $user;
$_SESSION["readonly"] = $auth->hasOnlyReadAccess();
$_SESSION["reducedwrite"] = $auth->hasReducedWrite();
$_SESSION["project_required"] = $auth->hasProjectRequired();
$_SESSION["person_id"] = $auth->getPersonId();
$_SESSION["can_see_secret"] = $auth->canSeeSecret();

$db = new DB();

switch ($action) {
    case "changes":
        $accPers = new AccountPerson($db);
        echo json_encode($accPers->allChangedSince($date));
        break;
    default:
        die("Unknown action $action");
}


?>
