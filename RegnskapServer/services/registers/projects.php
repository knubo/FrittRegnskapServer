<?php
/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<?php
/*
 * Created on Apr 12, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../classes/util/DB.php");
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/accounting/accountproject.php");

$db = new DB(); 
$accProj = new AccountProject($db);


$all = $accProj->getAll();

echo json_encode($all);
 
?>
