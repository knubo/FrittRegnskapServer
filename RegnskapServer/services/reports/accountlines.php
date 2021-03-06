<?php

/*
 * Created on Jul 11, 2007
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$fromdate = array_key_exists("fromdate", $_REQUEST) ? $_REQUEST["fromdate"] : 0;
$todate = array_key_exists("todate", $_REQUEST) ? $_REQUEST["todate"] : 0;
$account = array_key_exists("account", $_REQUEST) ? $_REQUEST["account"] : 0;
$project = array_key_exists("project", $_REQUEST) ? $_REQUEST["project"] : 0;
$person = array_key_exists("employee", $_REQUEST) ? $_REQUEST["employee"] : 0;
$description =  array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : 0;

if (!$fromdate && !$todate && !$account && !$project && !$person && !$description) {
	echo "[]";
    return;
}
$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accLine = new AccountLine($db);

$data = $accLine->searchLines($fromdate, $todate, $account, $project, $person, $description);

foreach($data as $one) {
    $one->fetchAllPosts();
}

echo json_encode($data);

?>
