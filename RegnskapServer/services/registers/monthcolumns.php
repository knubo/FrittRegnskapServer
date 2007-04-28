<?php
/*
 * Created on Apr 11, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountcolumn.php");
 
$db = new DB(); 
$acCols = new AccountColumn($db);

$columnList = $acCols->getAllColumns();

echo json_encode($columnList);
 
?>
