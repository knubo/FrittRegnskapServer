<?php
/*
 * Created on Apr 26, 2007
 *
 */
 
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountline.php");

$db = new DB();
$accLine = new AccountLine($db);

echo json_encode($accLine->listOfYearMonths()); 
?>
