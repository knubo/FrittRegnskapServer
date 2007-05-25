<?php
/*
 * Created on May 25, 2007
 *
 */
 
 $action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
 $line = array_key_exists("line", $_REQUEST) ? $_REQUEST["line"] : "268";
 
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountcount.php");

$db = new DB();
$accCount = new AccountCount($db);

echo json_encode($accCount->load($line)); 
 
?>
