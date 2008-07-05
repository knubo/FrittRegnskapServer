<?php

/*
 * Created on Aug 9, 2007
 *
 */

class Logger {

    private $db;

    function Logger($db) {
        $this->db = $db;
    }

    function log($category, $action, $message) {
        $prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "log (occured,username,category,action,message) values (now(),?,?,?,?)");
        $prep->bind_params("ssss", $_SESSION["username"], $category, $action, $message);
        $prep->execute();
    }
}
?>