<?php

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "init";

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/helpers/membersformatter.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

switch ($action) {
	case "init" :

        $result = array();
        $accYear = new AccountYearMembership($db);
        $accCourse = new AccountSemesterMembership($db, "course");
        $accTrain = new AccountSemesterMembership($db, "train");
        $accPrice = new AccountMemberPrice($db);

        $result["members"] = MembersFormatter::group($accYear->getOverview(), $accCourse->getOverview(),$accTrain->getOverview());
        $result["price"] = $accPrice->getAll();

        echo json_encode($result);
		break;
}
?>
