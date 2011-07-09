<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/import/personimportclass.php");
include_once ("../../classes/import/personimportpersisterclass.php");


$delimiter = array_key_exists("delimiter", $_REQUEST) ? $_REQUEST["delimiter"] : "";

header('Content-type: octet-stream');
header('Content-Disposition: attachment; filename="personexport.csv"');


$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();


$prep = $db->prepare("select id as memberid, firstname,lastname,email,address,postnmb,city,country,phone,cellphone,employee,birthdate,newsletter, gender from ".AppConfig::pre()."person where hidden=0");
$res = $prep->execute();

$headerExported = 0;

foreach($res as $one) {

    $keys = array_keys($one);

    if(!$headerExported) {
        $headerExported = 1;
        echo implode(",", $keys)."\n";
    }


    $first = 1;
    foreach($keys as $field) {


        if($first) {
            $first = 0;
        } else {
            echo ",";
        }

        switch($field) {
            case "newsletter":
            case "employee":
                echo $one[field];
                break;
            case "birthdate":

                if( preg_match( "/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $one["birthdate"])) {
                    $tmpdate = new eZDate();
                    $tmpdate->setMySQLDate($one["birthdate"]);
                    echo "\"".$tmpdate->displayAccount()."\"";
                } else {
                    echo "\"".$one["birthdate"]."\"";
                }
                break;
            default:
                echo "\"".$one[$field]."\"";
                break;
        }
    }
    echo "\n";
}
?>