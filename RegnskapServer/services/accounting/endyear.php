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
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/accounting/helpers/endyearhelper.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "status";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$acStandard = new AccountStandard($db);
$endYearHelper = new EndYearHelper($db);

switch ($action) {
	case "status" :
        $year = $acStandard->getOneValue(AccountStandard::CONST_YEAR);
	    
        $data = array();
        $data["data"] = $endYearHelper->getEndYearData($year);
        $data["readonly"] = $acStandard->getOneValue(AccountStandard::CONST_MONTH) != 12;
        
        
        echo json_encode($data);
		break;
    case "endyear" :
        $regnSession->checkWriteAccess();

        $acStandard = new AccountStandard($db);
        $db->begin();
        $semester = $acStandard->getOneValue(AccountStandard::CONST_SEMESTER);
        $year = $acStandard->getOneValue(AccountStandard::CONST_YEAR);
        $month = $acStandard->getOneValue(AccountStandard::CONST_MONTH);
        
        if($month != 12) {
            $db->rollback();
            header("HTTP/1.0 514 Illegal state");
            die("Can only end year in last month of year.");
        }
        
        $endYearHelper->endYear($year);
        
        $acStandard->setValue(AccountStandard::CONST_SEMESTER, ($semester + 1));
        $acStandard->setValue(AccountStandard::CONST_YEAR, ($year + 1));
        $acStandard->setValue(AccountStandard::CONST_MONTH, 1);
        
        $db->commit();
        echo json_encode("1");
        break;
     default:
        echo "Unknown action $action";
        break;

}
?>
