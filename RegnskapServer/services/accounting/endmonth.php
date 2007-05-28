<?php


/*
 * Created on May 28, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountposttype.php");
include_once ("../../classes/accounting/helpers/endmonthhelper.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";

$db = new DB();

$endHelper = new EndMonthHelper($db);

switch ($action) {
	case "status" :
		echo json_encode($endHelper->status());
		break;
	case "end" :
		$db->begin();
		$res = $endHelper->endMonth();
		$db->commit();
		echo "1";
		break;
}
?>
