<?php
/*
 * Created on Apr 26, 2007
 *
 */
 
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/auth/RegnSession.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accLine = new AccountLine($db);

echo json_encode($accLine->listOfYearMonths()); 
?>
