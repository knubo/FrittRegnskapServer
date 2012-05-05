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

    public function save($req) {
        if(!$req["id"]) {
            $prep = $this->db->prepare("insert into ". AppConfig::pre() . "invoice_type (description, invoice_type, split_type, email_from, reoccurance_interval, default_amount) values (?,?,?,?,?,?)");
            $prep->bind_params("siissd", $req["description"], $req["invoice_type"], $req["split_type"],$req["email_from"], $req["reoccurance_interval"], $req["default_amount"]);
            $prep->execute();
            return 1;
        } else {
            $prep = $this->db->prepare("update ". AppConfig::pre() . "invoice_type set description=?, invoice_type=?, split_type=?, email_from=?, reoccurance_interval=?, default_amount=? where id = ?");
            $prep->bind_params("siissdi", $req["description"], $req["invoice_type"], $req["split_type"],$req["email_from"], $req["reoccurance_interval"], $req["default_amount"], $req["id"]);
            $prep->execute();
            return 1;
        }

    }

    public function getEmailTemplate($id) {
        $prep = $this->db->prepare("select email_subject,email_body,email_header, email_footer, email_format from  ". AppConfig::pre() . "invoice_type where id = ?");
        $prep->bind_params("i", $id);
        $res = $prep->execute();

        return array_shift($res);

    }

    public function saveEmailTemplate($emailTemplate) {
        $prep = $this->db->prepare("update ". AppConfig::pre() . "invoice_type set email_subject = ?,email_body = ?,email_header = ?, email_footer = ?, email_format = ? where id = ?");
        $prep->bind_params("ssiisi", $emailTemplate->email_title, $emailTemplate->body, $emailTemplate->email_header, $emailTemplate->email_footer, $emailTemplate->email_format, $emailTemplate->id);
        $prep->execute();

        return 1;
    }

}

?>