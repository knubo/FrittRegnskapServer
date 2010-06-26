<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/excel/PHPExcel.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/import/accountsexportclass.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2008;
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "excel";
date_default_timezone_set(AppConfig::TIMEZONE);

$year = Strings::whitelist($year);

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

$export = new ExportAccounts($db, $year);

switch($action) {
    case "excel":
        $export->export();

        // Redirect output to a client's web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="regnskap_'.$year.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($export->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    case "raw":
        header('Content-type: octet-stream');
        header('Content-Disposition: attachment; filename="regnskap_'.$year.'.csv"');

        $res = $export->getYearPosts();


        foreach($res as $one) {

            $keys = array_keys($one);

            if(!$headerExported) {
                $headerExported = 1;
                echo preg_replace("/post_type/","account",implode(",", $keys)."\n");
            }


            $first = 1;
            foreach($keys as $field) {


                if($first) {
                    $first = 0;
                } else {
                    echo ",";
                }

                switch($field) {
                    case "id":
                    case "attachnmb":
                    case "month":
                    case "debet":
                    case "post_type":
                        echo $one[field];
                        break;
                    case "occured":
                        $tmpdate = new eZDate();
                        $tmpdate->setMySQLDate($one[$field]);
                        echo "\"".$tmpdate->displayAccount()."\"";
                        break;
                    default:
                        echo "\"".$one[$field]."\"";
                        break;
                }
            }
            echo "\n";
        }
}

?>