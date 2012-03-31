<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : 0;
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$semester = array_key_exists("semester", $_REQUEST) ? $_REQUEST["semester"] : 0;

$regnSession = new RegnSession(new DB());
$regnSession->auth();

$db = new DB(0, $regnSession->getSuperDBIfAny());



switch ($action) {
    case "year":
        $accYear = new AccountyearMembership($db);

        $title = " for &aring;r";
        $data = $accYear->missingMemberships($year);
        include("views/view_missing_memberships.php");
        break;

    case "semester":
        $accSemester = new AccountSemesterMembership($db);
        $title = " for semester";
        $data = $accSemester->missingMemberships($semester);
        include("views/view_missing_memberships.php");
        break;


}
?>
