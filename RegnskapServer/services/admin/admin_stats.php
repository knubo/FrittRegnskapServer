<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/admin/installer.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

if($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:".$regnSession->getPrefix());    
}

$master = new Master($db);

$installs = $master->getAllInstallations();

foreach($installs as &$one) {
    $dbins = new DB(0, DB::dbhash($one["hostprefix"]));
    
    $prefix = $one["dbprefix"];
    
    $prep = $dbins->prepare("select (select count(*) from ".$prefix."user) as user_count,".
	  "(select count(*) from ".$prefix."line) as line_count,".
	  "(select count(*) from ".$prefix."person) as person_count,".
	  "(select count(*) from ".$prefix."person where newsletter = 1) as newsletter_count,".	  
	  "(select count(*) from ".$prefix."year_membership) as member_count,".
	  "(select count(distinct(year)) from ".$prefix."year_membership) as year_count,".
	  "(select min(year) from ".$prefix."line) as first_year,".
	  "(select max(amount) from ".$prefix."year_price) as max_year_cost");
    
    $res = $prep->execute();
    
    $row = $res[0];
    
    $one["data"] = $row;
    $dbins->close();
    
}

echo json_encode($installs);
