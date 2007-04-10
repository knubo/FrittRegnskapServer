<?php

/* Does basic authentication and send back URL where the client should return. */

include_once("../conf/AppConfig.php");
include_once("../classes/auth/User.php"); 
include_once("../classes/util/DB.php");
include_once("../classes/auth/RegnSession.php");

    $db = new DB(); 
 	$sess = new RegnSession($db);   
    
	$user = $_GET["user"]; 
	
	$password = $_GET["password"];

	if(!$user || !$password) {
		die("Must supply user and password.");
	}
	
    $auth = new User($db);
    
    if($auth->authenticate($user, $password) == User::AUTH_OK) {
    	session_start();
    	$_SESSION["username"] = $user;
		$arr = array('url' => 'RegnskapClient/www/no.knubo.accounting.AccountingGWT/AccountingGWT.html');
		
    } else {
    	$arr = array('error' => 'Ulovlig brukernavn eller passord.');
    }
    echo json_encode($arr);
?>