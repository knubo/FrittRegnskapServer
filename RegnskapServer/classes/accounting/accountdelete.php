<?php


class AccountDelete {

    private $db;

    function AccountDelete($db) {
        if(!$db) {
            $db = new DB(); // For Code Assist
        }

        $this->db = $db;
    }


    function registerDeleteActionsAndSendEmail($input) {

        $prep = $this->db->prepare("insert into ");


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