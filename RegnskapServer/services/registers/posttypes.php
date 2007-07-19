<?php

/*
 * Created on Apr 11, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$disableFilter = array_key_exists("disableFilter", $_REQUEST) ? $_REQUEST["disableFilter"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

switch ($action) {
	case "all" :
		$acc = new AccountPostType($db);

		$columnList = $acc->getAll($disableFilter);
		echo json_encode($columnList);
        break;
}
?>
