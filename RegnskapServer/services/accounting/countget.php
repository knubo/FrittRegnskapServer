<?php
/*
 * Created on May 25, 2007
 *
 */
 
 $action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
 $line = array_key_exists("line", $_REQUEST) ? $_REQUEST["line"] : 0;
 
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountcount.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


$accCount = new AccountCount($db);

if(!$line) {
	return "";
}

echo json_encode($accCount->load($line)); 
 
?>
