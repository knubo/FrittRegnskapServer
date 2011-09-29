<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/auth/User.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$loggedInUser = $regnSession->auth();

$prep = $db->prepare("select * from installations");

$res = $prep->execute();

echo "<html><body><table>";

foreach ($res as $one) {
    $id = $one["id"];
    $secret = $one["secret"];

    echo "<tr><td>" . $one["hostprefix"] . "</td>";
    echo "<td><a href=\"http://master.local.no/RegnskapServer/services/admin/installs.php?action=delete&id=$id&secret=$secret\"" . ">Slett alt</a></td>";
    echo "<td><a href=\"http://master.local.no/RegnskapServer/services/admin/installs.php?action=deleteAccounting&id=$id&secret=$secret\"" . ">Slett Regnskapsdata</a></td>";
    echo "<td><a href=\"http://master.local.no/RegnskapServer/services/admin/installs.php?action=deletePeople&id=$id&secret=$secret\"" . ">Slett Personer</a></td>";
    echo "<td><a href=\"http://master.local.no/RegnskapServer/services/admin/installs.php?action=deletePeopleAndAccounting&id=$id&secret=$secret\"" . ">Slett Personer og regnskapsdata</a></td>";
    echo "</tr>";
}

echo "</table></body></html>";

?>
