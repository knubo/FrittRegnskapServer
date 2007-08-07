<?php
/*
 * Created on Aug 4, 2007
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/reporting/massletterhelper.php");
include_once ("../../pdf/class.ezpdf.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2007;
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$template = array_key_exists("template", $_REQUEST) ? $_REQUEST["template"] : 0;

$db = new DB(1);
$regnSession = new RegnSession($db);
$regnSession->auth();

if (!$year) {
	$standard = new AccountStandard($db);
	$year = $standard->getOneValue("STD_YEAR");
}

error_reporting(E_ALL);
set_time_limit(1800);

switch ($action) {
	case "pdf" :
		$massLetterHelper = new MassLetterHelper($db, $year);
		$massLetterHelper->useTemplate($template);
		break;
	case "list" :
        $massLetterHelper = new MassLetterHelper($db, $year);
        echo json_encode($massLetterHelper->listTemplates());
        break;
    default:
        die("Unknown action $action");
}
?>
