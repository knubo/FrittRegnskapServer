<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountstandard.php");
$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "current";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$yearPrice = array_key_exists("yearPrice", $_REQUEST) ? $_REQUEST["yearPrice"] : 0;
$yearYouthPrice = array_key_exists("yearPriceYouth", $_REQUEST) ? $_REQUEST["yearPriceYouth"] : 0;

$springCoursePrice = array_key_exists("springCoursePrice", $_REQUEST) ? $_REQUEST["springCoursePrice"] : 0;
$springTrainPrice = array_key_exists("springTrainPrice", $_REQUEST) ? $_REQUEST["springTrainPrice"] : 0;
$springYouthPrice = array_key_exists("springYouthPrice", $_REQUEST) ? $_REQUEST["springYouthPrice"] : 0;

$fallCoursePrice = array_key_exists("fallCoursePrice", $_REQUEST) ? $_REQUEST["fallCoursePrice"] : 0;
$fallTrainPrice = array_key_exists("fallTrainPrice", $_REQUEST) ? $_REQUEST["fallTrainPrice"] : 0;
$fallYouthPrice = array_key_exists("fallYouthPrice", $_REQUEST) ? $_REQUEST["fallYouthPrice"] : 0;

$accPrice = new AccountMemberPrice($db);

switch ($action) {
    case "current":
        $prices = $accPrice->getCurrentPrices();
        echo json_encode($prices);
        break;
    case "all" :
        $accSemester = new AccountSemester($db);

        $all = array();
        $all["price"] = $accPrice->getAll();
        $all["semesters"] = $accSemester->getAll();

        echo json_encode($all);
        break;
    case "save" :
        $regnSession->checkWriteAccess();
        $db->begin();
        $ret = $accPrice->save($year, $yearPrice, $springCoursePrice, $springTrainPrice, $springYouthPrice, $fallCoursePrice, $fallTrainPrice, $fallYouthPrice, $yearYouthPrice);

       	$db->commit();

        $result = array();
        $result["status"] = $ret ? 1 : 0;
        $result["year"] = $year;
        echo json_encode($result);
        break;
}
?>
