<?php


class AccountInvoice {
    private $db;


    function AccountInvoice($db) {
        if (!$db) {
            $db = new DB();
        }
        $this->db = $db;
    }

    public function getAll() {
        $prep = $this->db->prepare("select id, description, invoice_type, split_type, email_from, invoice_due_day, default_amount, (case when char_length(email_subject) > 5 and char_length(email_body) > 15 then 'true' else 'false' end) as emailOK from  " . AppConfig::pre() . "invoice_type");
        return $prep->execute();
    }

    public function save($req) {
        if (!$req["id"]) {
            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "invoice_type (description, invoice_type, split_type, email_from, invoice_due_day, default_amount) values (?,?,?,?,?,?)");
            $prep->bind_params("siissd", $req["description"], $req["invoice_type"], $req["split_type"], $req["email_from"], $req["invoice_due_day"], $req["default_amount"]);
            $prep->execute();
            return 1;
        } else {
            $prep = $this->db->prepare("update " . AppConfig::pre() . "invoice_type set description=?, invoice_type=?, split_type=?, email_from=?, invoice_due_day=?, default_amount=? where id = ?");
            $prep->bind_params("siissdi", $req["description"], $req["invoice_type"], $req["split_type"], $req["email_from"], $req["invoice_due_day"], $req["default_amount"], $req["id"]);
            $prep->execute();
            return 1;
        }

    }

    public function getEmailTemplate($id) {
        $prep = $this->db->prepare("select email_subject,email_body,email_header, email_footer, email_format from  " . AppConfig::pre() . "invoice_type where id = ?");
        $prep->bind_params("i", $id);
        $res = $prep->execute();

        return array_shift($res);

    }

    public function saveEmailTemplate($emailTemplate) {
        $prep = $this->db->prepare("update " . AppConfig::pre() . "invoice_type set email_subject = ?,email_body = ?,email_header = ?, email_footer = ?, email_format = ? where id = ?");
        $prep->bind_params("ssiisi", $emailTemplate->email_title, $emailTemplate->body, $emailTemplate->email_header, $emailTemplate->email_footer, $emailTemplate->email_format, $emailTemplate->id);
        $prep->execute();

        return 1;
    }

    public function getOne($id) {
        $prep = $this->db->prepare("select id, invoice_type, split_type, invoice_due_day, default_amount from " . AppConfig::pre() . "invoice_type where id = ?");
        $prep->bind_params("i", $id);

        $data = array_shift($prep->execute());

        return $data;
    }

    public function create_invoices($userId, $invoices, $receivers, $invoiceType) {
        $prep = $this->db->prepare("insert into  " . AppConfig::pre() . "invoice_top (created_by_person, created_date,invoice_type) values (?, now(),?)");
        $prep->bind_params("ii", $userId, $invoiceType);
        $prep->execute();
        $invoiceTopId = $this->db->insert_id();

        $ezDate = new eZDate();

        $prep = $this->db->prepare("insert into  " . AppConfig::pre() . "invoice (invoice_top, amount, due_date) values (?,?,?)");
        $prepRec = $this->db->prepare("insert into " . AppConfig::pre() . "invoice_recepiant (person_id, invoice_status, invoice_id , changed_by_person_id) values (?,?,?,?)");

        foreach($invoices as $invoice) {
            $ezDate->setTimeStamp($invoice->date / 1000);
            $prep->bind_params("ids", $invoiceTopId, Strings::money($invoice->amount), $ezDate->mySQLDate());
            $prep->execute();

            $invoiceId = $this->db->insert_id();

            foreach($receivers as $receiver) {
                $prepRec->bind_params("iiii", $receiver->id, 1, $invoiceId, $userId);
                $prepRec->execute();
            }
        }

    }

    public function invoicesNotSent() {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "invoice where id in (select invoice_id from " . AppConfig::pre() . "invoice_recepiant where invoice_status = 1)");
        return $prep->execute();
    }

}

?>