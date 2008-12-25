<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/auth/RegnSession.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2006;

$db = new DB(false);
$regnSession = new RegnSession($db);
$regnSession->auth();

$rep = new ReportYear($db);

$sum = 0;
$data = $rep->list_sums($year, '-1');
echo "<h1>Inntekter</h1>";
include("views/view_report_year.php");

$sumInntekter = $sum;
$sum = 0;

$data = $rep->list_sums($year, '1');
echo "<h1>Utgifter</h1>";
include("views/view_report_year.php");

echo "<h1>Resultat</h1>";
$resultat = $sumInntekter - $sum;
echo $resultat;

echo "<h1>Eiendeler, EK og forpliktelser</h1>";
echo "<h2>Debet</h2>";
$sum = 0;
$data = $rep->list_sums_3000($year, '1');
include("views/view_report_year.php");

$sumDeb = $sum;
echo "<h2>Kredit</h2>";
$sum = 0;
$data = $rep->list_sums_3000($year, '-1');
include("views/view_report_year.php");

echo "<h2>Sum</h2>";
$sum2000 = $sumDeb - $sum;
echo $sum2000;

?>
