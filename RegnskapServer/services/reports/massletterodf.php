<?php

include_once ("../../conf/AppConfig.php");
require_once('../../classes/odf/odf.php');
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/KID.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/reporting/massletterhelper.php");
include_once ("../../pdf/class.ezpdf.php");
include_once ("../../classes/auth/Master.php");


$preview = array_key_exists("preview", $_REQUEST) ? $_REQUEST["preview"] : 0;
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 2009;
$file = array_key_exists("file", $_REQUEST) ? $_REQUEST["file"] : "";

if($file[0] == '.') {
    die("Bad file name");
}

$db = new DB(0);
$regnSession = new RegnSession($db);
$regnSession->auth();

$db = new DB(0, DB::MASTER_DB);
$master = new Master($db);
$masterRecord = $master->get_master_record();

if(!$masterRecord) {
    $arr = array ('error' => 'Ikke identifisert database.');
    die(json_encode($arr));
}


$prefix = "";
if(AppConfig::USE_QUOTA) {
    $prefix = $regnSession->getPrefix()."/";
}

$openfile = "../../storage/".$prefix."/".$file;

if(!file_exists($openfile)) {
    die("Bad filename");
}

if(substr($openfile, -4) != ".odt") {
    die("Not an open office file");
}
$odf = new odf($openfile);

if($preview) {
    $accPerson = new AccountPerson($db);
    $users = $accPerson->getFirst();
} else {
    $accYearMem = new AccountYearMembership($db);
    $users = $accYearMem->getReportUsersFull($year);
}

$standard = new AccountStandard($db);
$dueDate = $standard->getOneValue(AccountStandard::CONST_MASSLETTER_DUE_DATE);
$year = $standard->getOneValue(AccountStandard::CONST_YEAR);
$adminEmail = $standard->getOneValue(AccountStandard::CONST_EMAIL_SENDER);
$accPrices = new AccountMemberPrice($db);
$prices = $accPrices->getCurrentPrices();
$yearprice = round($prices["year"]);
$courseprice = round($prices["course"]);
$trainprice = round($prices["train"]);

$kid = new KID();

$article = $odf->setSegment('page');

$date = new eZDate();
$charset = 'UTF-8';

foreach($users as $one) {
    $article->setVarsSilent("fornavn", $one["firstname"], 0, $charset);
    $article->setVarsSilent("etternavn", $one["lastname"],0, $charset);
    $article->setVarsSilent("adresse", $one["address"], 0, $charset);
    $article->setVarsSilent("epost", $one["email"], 0, $charset);
    $article->setVarsSilent("by", $one["city"], 0, $charset);
    $article->setVarsSilent("postnr", $one["postnmb"], 0, $charset);
    $article->setVarsSilent("telefon", $one["phone"], 0, $charset);
    $article->setVarsSilent("mobil", $one["cellphone"], 0, $charset);
    $article->setVarsSilent("medlemsnr", $one["id"], 0, $charset);
    $article->setVarsSilent("kid", $kid->generateKIDmod10($masterRecord[id], 4, $one["id"],5), 0, $charset);

    if ($one["birthdate"] && $one["birthdate"] != "0000-00-00") {
        $date->setMySQLDate($one["birthdate"]);
        $article->fodselsdato($date->display());
    } else {
        $article->fodselsdato("mangler");
    }
    $article->kjonn($one["gender"] == 'M' ? "Mann" : "Kvinne");

    $article->forfallsdato($dueDate);
    $article->aarspris($yearprice);
    $article->kurspris($courseprice);
    $article->treningspris($trainprice);
    $article->ar($year);

    $article->merge();
}

$odf->mergeSegment($article);

// We export the file
$odf->exportAsAttachedFile();

?>