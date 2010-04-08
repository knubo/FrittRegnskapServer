<?php 

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/admin/installer.php");


$db = new DB();
$installer = new Installer($db);

$installer->dropTables("wj");
$installer->createTables("wj");
$installer->createIndexes("wj");
$installer->addAccountPlan("wj");

echo "OK";


?>