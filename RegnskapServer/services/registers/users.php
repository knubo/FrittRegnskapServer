<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/User.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/accounting/accountstandard.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$user = array_key_exists("username", $_REQUEST) ? $_REQUEST["username"] : "";
$password = array_key_exists("password", $_REQUEST) ? $_REQUEST["password"] : "";
$person = array_key_exists("person", $_REQUEST) ? $_REQUEST["person"] : "";
$readonly = array_key_exists("readonly", $_REQUEST) ? $_REQUEST["readonly"] : "";
$reducedwrite = array_key_exists("reducedwrite", $_REQUEST) ? $_REQUEST["reducedwrite"] : "";
$project_required = array_key_exists("project_required", $_REQUEST) ? $_REQUEST["project_required"] : "";
$see_secret = array_key_exists("see_secret", $_REQUEST) ? $_REQUEST["see_secret"] : "";


$db = new DB();
$regnSession = new RegnSession($db);
$loggedInUser = $regnSession->auth();

switch ($action) {
    case "all" :
        $accUsers = new User($db);
        $columnList = $accUsers->getAll();
        echo json_encode($columnList);
        break;
    case "save" :
        $res = array ();

        if($see_secret && !$regnSession->canSeeSecret()) {
            header("HTTP/1.0 513 Validation Error");
            die(json_encode(array("MISSING_ACCESS")));
        }

        $accUsers = new User($db);

        if(($user == $loggedInUser) && $regnSession->canSeeSecret() && (!$see_secret) && $accUsers->isOnlyOneUserWithSecretAccess()) {
            header("HTTP/1.0 513 Validation Error");
            die(json_encode(array("LAST_USER")));
        }

        $userToChange = $accUsers->getOne($user);

        $changeUserSecretInvalid = $userToChange && $userToChange["see_secret"] != $see_secret && !$regnSession->canSeeSecret();
        $newUserSecretInvalid = !$userToChange && $see_secret && !$regnSession->canSeeSecret();
        
        if($changeUserSecretInvalid || $newUserSecretInvalid) {
            header("HTTP/1.0 513 Validation Error");
            die(json_encode(array("MISSING_ACCESS")));
        }

        if($loggedInUser == $user && $regnSession->hasReducedWriteAccess()) {
            $rowsAffected = $accUsers->updatePassword($user, $password);
            $res["result"] = $rowsAffected;
        } else {
            $regnSession->checkWriteAccess();
            $rowsAffected = $accUsers->save($user, $password, $person,$readonly, $reducedwrite, $project_required, $see_secret);
            $res["result"] = $rowsAffected;
        }

        echo json_encode($res);
        break;
    case "delete" :
        $regnSession->checkWriteAccess();
        
        $accStandard = new AccountStandard($db);

        $abortDelete = $accStandard->getOneValue(AccountStandard::CONST_NO_DELETE_USERS);
        
        if($abortDelete) {
            header("HTTP/1.0 513 Validation Error");
            die(json_encode(array("DELETE_DISABLED")));
        }
        
        $accUsers = new User($db);
        $rowsAffected = $accUsers->delete($user);
        $res = array ();
        $res["result"] = $rowsAffected;
        echo json_encode($res);
        break;
}
?>