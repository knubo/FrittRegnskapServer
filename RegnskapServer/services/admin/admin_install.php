<?php 

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/admin/installer.php");


$db = new DB();
$installer = new Installer($db);

$installer->createTables("wj_");
$installer->createIndexes("wj_");
$installer->addAccountPlan("wj_");

echo "OK";


?>