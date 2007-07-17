<?php

/*
 * Created on Jul 16, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/User.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$user = array_key_exists("username", $_REQUEST) ? $_REQUEST["username"] : "";
$password = array_key_exists("password", $_REQUEST) ? $_REQUEST["password"] : "";
$person = array_key_exists("person", $_REQUEST) ? $_REQUEST["person"] : "";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
 
switch ($action) {
	case "all" :
		$accUsers = new User($db);
		$columnList = $accUsers->getAll();
		echo json_encode($columnList);
		break;
	case "save" :
		$accUsers = new User($db);
		$rowsAffected = $accUsers->save($user, $password, $person);
		$res = array ();
		$res["result"] = $rowsAffected;
		echo json_encode($res);
        break;  
	case "delete" :
        $accUsers = new User($db);
        $rowsAffected = $accUsers->delete($user);
        $res = array ();
        $res["result"] = $rowsAffected;
        echo json_encode($res);

		}
?>
