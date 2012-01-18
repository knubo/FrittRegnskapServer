<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/util/fileutil.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountstandard.php");

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$user = $regnSession->auth();

$regnSession->checkWriteAccess();

$accPerson = new AccountPerson($db);

$file = $_FILES['uploadFormElement']['tmp_name'];

if (!AppConfig::USE_QUOTA) {
    die("Kun tilgjenglig om man benytter quota innstilling.");
}
$prefix = $regnSession->getPrefix() . "/";

if (!is_dir("../../storage/$prefix")) {
    mkdir("../../storage/$prefix", 0700, true);
}

$newFile = "../../storage/$prefix/upload_" . $user . ".csv";

$separator = $_REQUEST["separator"];

if(!$separator) {
    die("Fikk ikke forventet skilletegn. Fikk: $separator");
}

echo "<h2>Disse linjene er tatt bort:</h2>";

echo "<pre>";

$writeHandle = @fopen($newFile, "w") or die("Kan ikke skrive til fil $newFile");

$handle = @fopen($file, "r");

$count = 0;

if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {

        $parts = explode($separator, $buffer);

        $found = 0;
        foreach ($parts as $part) {
            if (strpos($part, "@") > 0) {
                if ($accPerson->emailExists($part)) {
                    echo "$buffer";
                    $found = 1;
                    break;
                }
            }
        }
        if(!$found) {
           fwrite($writeHandle, $buffer);
        }

        if ($count++ > 1000) {
            fclose($writeHandle);
            $writeHandle = @fopen($newFile, "w") or die("Kan ikke skrive til fil");
            fclose($writeHandle);
            die("For mange adresser i filen. Maks 1000 elementer filtreres.");
        }
        //echo $buffer;
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}
fclose($writeHandle);

unlink($file);

echo "</pre>";

echo "Under er de ferdig filtrerte personradene i en ramme (iframe)."
?>