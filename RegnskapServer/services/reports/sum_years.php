<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/reporting/report_year.php");
include_once ("../../classes/auth/RegnSession.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2005;

$db = new DB(false);
$regnSession = new RegnSession($db);
$regnSession->auth();

$rep = new ReportYear($db);

$sum = 0;
$data = $rep->list_sums_earnings($year);
echo "<h1>Inntekter</h1>";
include("views/view_report_year.php");

$sumInntekter = $sum;
$sum = 0;
$data = $rep->list_sums_cost($year);
echo "<h1>Utgifter</h1>";
include("views/view_report_year.php");

echo "<h1>Resultat</h1>";
$resultat = $sumInntekter - $sum;
echo $resultat;

echo "<h1>EK og forpliktelser</h1>";
$sum = 0;
$data = $rep->list_sums_ownings($year);
include("views/view_report_year.php");

echo "<h2>Sum</h2>";
echo $sum;

echo "<h1>Eiendeler</h1>";
$data = $rep->list_sums_2000($year);
include("views/view_report_year_ownings.php");

?>
