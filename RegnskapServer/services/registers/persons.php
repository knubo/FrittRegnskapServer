<?php
/*
 * Created on Apr 12, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../classes/util/DB.php");
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/accounting/accountperson.php");

if(array_key_exists("onlyemp", $_GET)) { 
   $onlyEmp = $_GET["onlyemp"];
} else {
	$onlyEmp = 0;
}

 
$db = new DB(); 
$accPers = new AccountPerson($db);


$columnList = $accPers->getAll($onlyEmp);

echo json_encode($columnList);
 
?>
