<?php
/*
 * Created on May 19, 2007
 *
 */
include_once ("../../conf/AppConfig.php");

$ret = array();

$ret["values"] = AppConfig::CountValues();
$ret["columns"] = AppConfig::CountColumns();

echo json_encode($ret);

?>
