<?php
/*
 * Created on May 19, 2007
 *
 */
include_once ("../../conf/AppConfig.php");

echo json_encode(AppConfig::CountValues());

?>
