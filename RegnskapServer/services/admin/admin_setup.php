<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/admin/installer.php");
include_once ("../../classes/auth/User.php");

$admin = array_key_exists("admin", $_REQUEST) ? $_REQUEST["admin"] : "";
$password = array_key_exists("password", $_REQUEST) ? $_REQUEST["password"] : "";
$wikilogin = array_key_exists("wikilogin", $_REQUEST) ? $_REQUEST["wikilogin"] : "";

if(!$admin || !$password || !$wikilogin) {
    die("Must provide admin, wikilogin and password parameters.");
}

$db = new DB();
$installer = new Installer($db);

$dbprefix = "master";

/* Careful with this one, double security both commented out and not set to run. */
if(1) {$installer->dropTables($dbprefix); }

if(!$db->table_exists("master_person")) {
    $installer->createTables($dbprefix);
    $installer->createIndexes($dbprefix);
    $installer->addAccountPlan($dbprefix);
    $installer->addStandardData($dbprefix);

    $installer->addAdminUser($admin, $password);
    $installer->addMasterToInstallations($wikilogin);
    
    echo "Data added";
} else {
   echo "No action";
}

?>