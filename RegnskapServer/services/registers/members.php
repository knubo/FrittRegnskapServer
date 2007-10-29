<?php


/*
 * Created on May 1, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "overview";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "0";
$semester = array_key_exists("semester", $_REQUEST) ? $_REQUEST["semester"] : "0";
$personId = array_key_exists("personId", $_REQUEST) ? $_REQUEST["personId"] : "0";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$standard = new AccountStandard($db);
$semesterAcc = new AccountSemester($db);

if (!$year) {
	$year = $standard->getOneValue("STD_YEAR");
}

if (!$semester) {
	$semester = $standard->getOneValue("STD_SEMESTER");
}

$result = array ();

switch ($action) {
	case "deleteyear" :
		$regnSession->checkWriteAccess();
		$acc = new AccountYearMembership($db);
        $res = $acc->delete($year, $personId);

        $result = array("result" => $res);
		break;
	case "deletetrain" :
		$regnSession->checkWriteAccess();
		$acc = new AccountSemesterMembership($db, "train");
        $res = $acc->delete($semester, $personId);

        $result = array("result" => $res);
		break;
	case "deletecourse" :
		$regnSession->checkWriteAccess();
		$acc = new AccountSemesterMembership($db, "course");
        $res = $acc->delete($semester, $personId);

        $result = array("result" => $res);
		break;
	case "year" :
		$acc = new AccountYearMembership($db);
		$result["members"] = $acc->getAllMemberNames($year);
		$result["year"] = $year;
		$result["text"] = $year;
		break;
	case "class" :
		$acc = new AccountSemesterMembership($db, "course");
		$result["members"] = $acc->getAllMemberNames($semester);
		$result["semester"] = $semester;
		$result["text"] = $semesterAcc->getSemesterName($semester);
		break;
	case "training" :
		$acc = new AccountSemesterMembership($db, "train");
		$result["members"] = $acc->getAllMemberNames($semester);
		$result["semester"] = $semester;
		$result["text"] = $semesterAcc->getSemesterName($semester);
		break;
     case "overview":
        $accYear = new AccountYearMembership($db);
        $accCourse = new AccountSemesterMembership($db, "course");
        $accTrain = new AccountSemesterMembership($db, "train");
        
        $result["year"] = $accYear->getOverview();   
        $result["train"] = $accTrain->getOverview();   
        $result["course"] = $accCourse->getOverview();   
        
        break;
	default :
		die("Unknown action $action");
}
echo json_encode($result);
?>
