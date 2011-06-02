<?php
include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/Master.php");
include_once ("../classes/accounting/accountperson.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/util/ezdate.php");
include_once ("../classes/util/strings.php");
include_once ("../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "";
$email = array_key_exists("email", $_REQUEST) ? $_REQUEST["email"] : "";

if($action != "forgotten") {
    die("Illegal action");
}

if(!$email) {
    //  sleep(10);
    die("No email provided");
}
//sleep(1);

date_default_timezone_set(AppConfig::TIMEZONE);

$db = new DB(0, DB::MASTER_DB);
$master = new Master($db);
$masterRecord = $master->get_master_record();

if(!$masterRecord) {
    $arr = array (
				'error' => 'Ikke identifisert database.'
				);
				echo json_encode($arr);
				break;
}
$dbu = new DB();

$dbp = $masterRecord["parentdbprefix"] ? $masterRecord["parentdbprefix"] : $masterRecord["dbprefix"];


$accPerson = new AccountPerson($dbu, $dbp);

$time = time() + (60 * 30);

$timestring = date("d.m.Y G:i", $time);


$secret = $time."-";

while(strlen($secret) < 40) {
    $secret.= chr(mt_rand(97, 122));
}

if(strpos($email, "@") > 0) {
    $res = $accPerson->updateSecretIfUserMatches($email, $secret);
} else {
    $res = $accPerson->updateSecretIfUserExists($email, $secret);
}

$emailer = new Emailer();
if($res["error"]) {
    $emailer->sendEmail("Mislykket glemt passord", "admin@frittregnskap.no", "Detaljer er:".json_encode($res),"admin@frittregnskap.no",0);
    die(json_encode(array("status" => 0, "res" => $res)));
}

$user = $res["username"];

$body = "Linken under gir direkte innlogging til regnskapssystemet i Fritt Regnskap. Etter innlogging kan du endre til et nytt passord.\n\n".
             "http://".$_SERVER["SERVER_NAME"]."/RegnskapServer/services/authenticate.php?action=secret&username=$user&secret=".$secret.
             "\n\nDenne linken er kun gyldig en gang de neste 30 minuttene fra tidspunktet du benyttet glemt passord.\n\n".
            "Om du mottar denne eposten og ikke har benyttet glemt passord kan du ignorere denne eposten og ditt passord forblir uendret.".
             "\n\nVenligst hilsen Fritt Regnskap.\n\n";

$emailer->sendEmail("Glemt passord - Fritt Regnskap", $email, $body, "admin@frittregnskap.no",0,0,0, "admin@frittregnskap.no");

echo json_encode(array("status" => 1, "res" => $res));


?>