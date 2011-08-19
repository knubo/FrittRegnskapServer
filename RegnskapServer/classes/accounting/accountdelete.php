<?php


class AccountDelete {

    private $db;
    private $masterId;

    function AccountDelete($db, $masterId) {
        if(!$db) {
            $db = new DB(); // For Code Assist
        }

        $this->db = $db;
        $this->masterId = $masterId;
    }


    function registerDeleteActionsAndSendEmail($input) {

        $prep = $this->db->prepare("insert into change_request (installation_id, action, addedTime,addedBy, reason) values (?,?,now(), ?)");


        $emailer = new Emailer();

        $status = $emailer->sendEmail($this->buildSubject($input), $input["to"], $this->buildBody($input), $input["from"], 0);

        return $status;
}

    private function buildSubject($input) {
    }

    private function buildBody($input) {
    }
}

?>