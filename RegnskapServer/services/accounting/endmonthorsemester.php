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
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


$endHelper = new EndMonthHelper($db);

switch ($action) {
	case "test":
        $acStandard = new AccountStandard($db);
        $acPostType = new AccountPostType($db);
        $endPostIds = $acStandard->getOneValueAsArray(AccountStandard::CONST_END_MONTH_TRANSFER_POSTS);
        $endPosts = $acPostType->getSomeIndexedById($endPostIds);
        echo json_encode($endPosts);
        break;
	case "status" :
        $acStandard = new AccountStandard($db);

        $res = array();
        $res["posts"] = $endHelper->status();
        $res["year"] = $acStandard->getOneValue("STD_YEAR");;
        $res["month"] = $acStandard->getOneValue("STD_MONTH");
		echo json_encode($res);
		break;
    case "endsemester" :
        $regnSession->checkWriteAccess();

        $acStandard = new AccountStandard($db);
        $db->begin();
        $res = $endHelper->endMonth();
        $semester = $acStandard->getOneValue("STD_SEMESTER");
        $acStandard->setValue("STD_SEMESTER", ($semester + 1));
        $db->commit();
        echo json_encode("1");
        break;
	case "endmonth" :
        $regnSession->checkWriteAccess();
		$db->begin();
		$res = $endHelper->endMonth();
		$db->commit();
		echo json_encode("1");
		break;
     default:
        echo "Unknown action $action";
        break;

}
?>
