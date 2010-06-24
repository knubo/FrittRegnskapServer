<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/excel/PHPExcel.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/import/accountsexportclass.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2008;
date_default_timezone_set(AppConfig::TIMEZONE);

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

$export = new ExportAccounts($db, $year);

// Redirect output to a client's web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="regnskap_$year.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($export->objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;




?>