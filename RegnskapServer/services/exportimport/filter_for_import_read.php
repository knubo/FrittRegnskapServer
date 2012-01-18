<?php


include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$user = $regnSession->auth();

$regnSession->checkWriteAccess();

header('Content-Type: text/plain');

if (!AppConfig::USE_QUOTA) {
    die("Kun tilgjenglig om man benytter quota innstilling.");
}
$prefix = $regnSession->getPrefix() . "/";

$file = "../../storage/$prefix/upload_" . $user . ".csv";

echo file_get_contents($file);

unlink($file);
?>