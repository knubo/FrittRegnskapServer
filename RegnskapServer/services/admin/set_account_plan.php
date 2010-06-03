<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/admin/installer.php");

$db = new DB();
$installer = new Installer($db);

$dbprefix = array_key_exists("dbprefix", $_REQUEST) ? $_REQUEST["dbprefix"] : "";
if(0 && $dbprefix) {

    /* Careful with this one, double security both commented out and not set to run. */
    //if(0) {$installer->dropTables($dbprefix); }

    $prep = $db->prepare("delete from ".$dbprefix."_post_type");
    $prep->execute();

    $prep = $db->prepare("delete from ".$dbprefix."_coll_post_type");
    $prep->execute();

    $prep = $db->prepare("delete from ".$dbprefix."_detail_post_type");
    $prep->execute();

    $installer->addAccountPlan($dbprefix);
    echo "OK $dbprefix";
} else {
    echo "Nonono...";
}
?>