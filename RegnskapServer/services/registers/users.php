<?php

/*
 * Created on Jul 16, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/User.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$user = array_key_exists("username", $_REQUEST) ? $_REQUEST["username"] : "";
$password = array_key_exists("password", $_REQUEST) ? $_REQUEST["password"] : "";
$person = array_key_exists("person", $_REQUEST) ? $_REQUEST["person"] : "";
$readonly = array_key_exists("readonly", $_REQUEST) ? $_REQUEST["readonly"] : "";
$reducedwrite = array_key_exists("reducedwrite", $_REQUEST) ? $_REQUEST["reducedwrite"] : "";
$project_required = array_key_exists("project_required", $_REQUEST) ? $_REQUEST["project_required"] : "";


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

        if($loggedInUser == $user && $regnSession->hasReducedWriteAccess()) {
            $accUsers = new User($db);
            $rowsAffected = $accUsers->updatePassword($user, $password);
            $res["result"] = $rowsAffected;
        } else {
            $regnSession->checkWriteAccess();
    		$accUsers = new User($db);
    		$rowsAffected = $accUsers->save($user, $password, $person,$readonly, $reducedwrite, $project_required);        	
    		$res["result"] = $rowsAffected;
        }
    
		echo json_encode($res);
        break;  
	case "delete" :
        $regnSession->checkWriteAccess();
        $accUsers = new User($db);
        $rowsAffected = $accUsers->delete($user);
        $res = array ();
        $res["result"] = $rowsAffected;
        echo json_encode($res);

		}
?>
