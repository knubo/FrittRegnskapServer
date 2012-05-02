<?php


class AccountInvoice {
	private $db;


	function AccountInvoice($db) {
        if(!$db) {
            $db = new DB();
        }
        $this->db = $db;
	}

    public function getAll() {
        $prep = $this->db->prepare("select id, description, invoice_type, split_type, email_from, reoccurance_interval, default_amount, (case when char_length(email_subject) > 5 and char_length(email_body) > 15 then 'true' else 'false' end) as emailOK from  ". AppConfig::pre() . "invoice_type");
        return $prep->execute();
    }

}

?>