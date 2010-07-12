<?php
/*
 * Created on Aug 4, 2007
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/reporting/massletterhelper.php");
include_once ("../../pdf/class.ezpdf.php");
include_once ("../../classes/auth/Master.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2007;
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$template = array_key_exists("template", $_REQUEST) ? $_REQUEST["template"] : 0;
$data = array_key_exists("data", $_REQUEST) ? $_REQUEST["data"] : 0;


$db = new DB(1);
$regnSession = new RegnSession($db);
$regnSession->auth();

$prefix = $regnSession->getPrefix();

$standard = new AccountStandard($db);

if (!$year) {
    $year = $standard->getOneValue(AccountStandard::CONST_YEAR);
}

$accPrices = new AccountMemberPrice($db);
$prices = $accPrices->getCurrentPrices();

$yearprice = round($prices["year"]);
$courseprice = round($prices["course"]);
$trainprice = round($prices["train"]);
$dueDate = $standard->getOneValue("MASSLETTER_DUE_DATE");

error_reporting(E_ALL);
set_time_limit(1800);

$massLetterHelper = new MassLetterHelper($db, $year, $yearprice, $courseprice, $trainprice, $dueDate, $prefix, $regnSession);
switch ($action) {
    case "pdf" :
        $massLetterHelper->useTemplate($template);
        break;
    case "fonts":
        $arr = array();
        foreach(scandir("../../pdf/fonts/") as $one) {
            if($one[0] == '.') {
                continue;
            }
            $arr[] = substr($one, 0, -4);
        }
        usort($arr, function($a, $b) {
            $al = strtolower($a);
            $bl = strtolower($b);
            if ($al == $bl) {
                return 0;
            }
            return ($al > $bl) ? +1 : -1;
        });
        
        echo json_encode($arr);
        break;

    case "images":
        $arr = array();
        foreach(scandir("../../storage/$prefix/") as $one) {
            if($one[0] == '.') {
                continue;
            }
            $type = substr($one, -4);

            if($type != ".png" && $type != ".gif" && $type != ".jpg" && $type != "jpeg") {
                continue;
            }

            $arr[] = $one;
        }
        sort($arr);
        echo json_encode($arr);
        break;
         
    case "preview":
        $massLetterHelper->useTemplate($template, 1);
        system(AppConfig::CONVERT." ../../storage/$prefix/massletter_preview.pdf ../../storage/$prefix/massletter_preview.png");
        header("Content-Type: image/png");
        readfile("../../storage/$prefix/massletter_preview.png");
        break;
    case "list" :
        echo json_encode($massLetterHelper->listTemplates());
        break;
    case "source":
        echo $massLetterHelper->readTemplate($template);
        break;
    case "save":
        echo $massLetterHelper->saveTemplate($template, $data);
        break;
    default:
        die("Unknown action $action");
}
?>
