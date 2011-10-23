<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/auth/User.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$loggedInUser = $regnSession->auth();

$prep = $db->prepare("select * from sqllist where verified = 0");

$res = $prep->execute();

echo "<html><body><table>";

foreach ($res as $one) {
    $id = $one["id"];
    $secret = $one["secret"];

    echo "<tr><td>" . $id . "</td>";
    echo "<td><a href=\"http://master.local.no/RegnskapServer/services/admin/admin_sql.php?action=verify&id=$id&secret=$secret\"" . ">Godkjenn</a></td>";
    echo "</tr>";
}

echo "</table></body></html>";

?>
