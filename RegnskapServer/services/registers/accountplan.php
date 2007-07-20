<?php
/*
 * Created on Jul 20, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountplan.php");
include_once ("../../classes/auth/RegnSession.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accPlan = new AccountPlan($db);

$all = $accPlan->getCollectionPosts();

echo json_encode($all);

?>
