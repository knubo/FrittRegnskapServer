<?php
 
include_once ("../../conf/Version.php");
$arr = array();
$arr["serverversion"] = Version::SERVER_VERSION;

echo json_encode($arr);
?>
