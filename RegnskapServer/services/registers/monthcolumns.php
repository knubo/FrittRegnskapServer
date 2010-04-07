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
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
 
$db = new DB(); 
$regnSession = new RegnSession($db);
$regnSession->auth();

$acCols = new AccountColumn($db);

$columnList = $acCols->getAllColumns();

echo json_encode($columnList);
 
?>
