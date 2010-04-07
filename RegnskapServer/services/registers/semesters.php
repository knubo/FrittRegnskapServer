<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "";
$fall = array_key_exists("fall", $_REQUEST) ? $_REQUEST["fall"] : "";
$spring = array_key_exists("spring", $_REQUEST) ? $_REQUEST["spring"] : "";

$accSemester = new AccountSemester($db);

switch ($action) {
	case "all" :
		$all = $accSemester->getAll();
		echo json_encode($all);
		break;
	case "save" :
        $db->begin();
        $res = $accSemester->save($year, $fall, $spring);
        $db->commit();

        $arr = array();
        $arr["result"] = $res;
        echo json_encode($arr);
		break;
}
?>
