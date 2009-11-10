<?php


/*
 * Created on May 28, 2007
 *
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


$endHelper = new EndMonthHelper($db);

switch ($action) {
	case "status" :
        $reportYear = new ReportYear();
	    $res = array();
        $year = $acStandard->getOneValue(AccountStandard::CONST_SEMESTER);
	    
        
        
        echo json_encode($res);
		break;
    case "endyear" :
        $regnSession->checkWriteAccess();

        $acStandard = new AccountStandard($db);
        $db->begin();
        $semester = $acStandard->getOneValue(AccountStandard::CONST_SEMESTER);
        $acStandard->setValue(AccountStandard::CONST_SEMESTER, ($semester + 1));
        $db->commit();
        echo json_encode("1");
        break;
     default:
        echo "Unknown action $action";
        break;

}
?>
