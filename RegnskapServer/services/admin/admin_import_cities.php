<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/admin/CityAddress.php");


$authDB = new DB();
$logger = new Logger($authDB);
$regnSession = new RegnSession($authDB);
$regnSession->auth();

if ($regnSession->getPrefix() != "master_") {
    die("Not authenticated for master database:" . $regnSession->getPrefix());
}

$db = new DB(0, AppConfig::DB_HASH_POSTCODES);

$accCities = new CityAddress($db);

echo json_encode($_FILES);

$fileName = $_FILES['uploadFormElement']['tmp_name'];

$handle = @fopen($fileName, "r");
if ($handle) {
    $db->begin();

    $accCities->deleteAll();

    while (($buffer = fgets($handle)) !== false) {
        $accCities->insert($buffer);
    }

    $db->commit();


    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
} else {
    echo "Failed to read file $fileName";
}


?>