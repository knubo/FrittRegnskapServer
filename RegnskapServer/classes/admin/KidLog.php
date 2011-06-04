<?php

class KidLog {
    private $db;

    function KidLog($db) {
        if(!$db) {
            $db = new DB();
        }

        $this->db = $db;
    }

    function log($filename, $transaction_count, $install) {
        $prep = $this->db->prepare("insert into kid_log (owning_install, occured, transaction_count, transaction_file) values (?,now(),?,?)");

        $prep->bind_params("isis", $install["id"], $transaction_count, $filename);

        $prep->execute();
    }

}
