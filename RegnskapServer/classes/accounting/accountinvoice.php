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
        $prep = $this->db->prepare("select id, description, split_type, email_from, invoice_due_day, (case when char_length(email_subject) > 5 and char_length(email_body) > 15 then 'true' else 'false' end) as emailOK, invoice_template from  " . AppConfig::pre() . "invoice_type");
        return $prep->execute();
    }

    public function save($req) {
        $credPost = $req["credit_post_type"];
        if (!$credPost) {
            $credPost = null;
        }

        if (!$req["id"]) {
            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "invoice_type (description, invoice_type, split_type, email_from, invoice_due_day, default_amount, credit_post_type,invoice_template) values (?,?,?,?,?,?,?,?)");
            $prep->bind_params("siissdis", $req["description"], $req["invoice_type"], $req["split_type"], $req["email_from"], $req["invoice_due_day"], $req["default_amount"], $credPost, $req["invoice_template"]);
            $prep->execute();
            return 1;
        } else {
            $prep = $this->db->prepare("update " . AppConfig::pre() . "invoice_type set description=?, invoice_type=?, split_type=?, email_from=?, invoice_due_day=?, default_amount=?, credit_post_type=?,invoice_template=? where id = ?");
            $prep->bind_params("siissdisi", $req["description"], $req["invoice_type"], $req["split_type"], $req["email_from"], $req["invoice_due_day"], $req["default_amount"], $credPost, $req["invoice_template"], $req["id"]);
            $prep->execute();
            return 1;
        }

    }

    public function getEmailTemplate($id) {
        $prep = $this->db->prepare("select email_subject,email_body,email_header, email_footer, email_format,(case when char_length(email_subject) > 5 and char_length(email_body) > 15 then 'true' else 'false' end) as emailOK from  " . AppConfig::pre() . "invoice_type where id = ?");
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

        $result = $prep->execute();
        $data = array_shift($result);

        return $data;
    }

    public function create_invoices($userId, $invoices, $receivers, $invoiceType) {
        $prep = $this->db->prepare("insert into  " . AppConfig::pre() . "invoice_top (created_by_person, created_date,invoice_type) values (?, now(),?)");
        $prep->bind_params("ii", $userId, $invoiceType);
        $prep->execute();
        $invoiceTopId = $this->db->insert_id();

        $ezDate = new eZDate();

        $prep = $this->db->prepare("insert into  " . AppConfig::pre() . "invoice (invoice_top, amount, due_date) values (?,?,?)");
        $prepRec = $this->db->prepare("insert into " . AppConfig::pre() . "invoice_recipient (person_id, invoice_status, invoice_id , changed_by_person_id) values (?,?,?,?)");

        foreach ($invoices as $invoice) {
            $ezDate->setTimeStamp($invoice->date / 1000);
            $prep->bind_params("ids", $invoiceTopId, Strings::money($invoice->amount), $ezDate->mySQLDate());
            $prep->execute();

            $invoiceId = $this->db->insert_id();

            foreach ($receivers as $receiver) {
                $prepRec->bind_params("iiii", $receiver->id, 1, $invoiceId, $userId);
                $prepRec->execute();
            }
        }

    }

    public function invoicesNotSent() {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "invoice where id in (select invoice_id from " . AppConfig::pre() . "invoice_recipient where invoice_status = 1)");
        return $prep->execute();
    }

    public function invoice($recevierId) {
        $prep = $this->db->prepare("select firstname, lastname, email, TY.description, R.id,invoice_status, sent_date, deleted_date, person_id, amount, due_date, TY.id as template_id, I.credit_post_type from " . AppConfig::pre() . "invoice_recipient R, " . AppConfig::pre() . "invoice I, " . AppConfig::pre() . "invoice_top T, " . AppConfig::pre() . "invoice_type TY, " . AppConfig::pre() . "person P " .
                " where R.invoice_id = I.id and I.invoice_top = T.id and T.invoice_type = TY.id and person_id = P.id and R.id = ?");
        $prep->bind_params("i", $recevierId);

        $result = $prep->execute();
        $res = array_shift($result);

        return $res;

    }

    /* From one invoice, find template and return all that can match */
    public function invoicesForODF($invoice_template) {
        $prep = $this->db->prepare("select firstname, lastname, address, postnmb, city, country, R.id, person_id, amount, due_date, TY.id as template_id from " . AppConfig::pre() . "invoice_recipient R, " . AppConfig::pre() . "invoice I, " . AppConfig::pre() . "invoice_top T, " . AppConfig::pre() . "invoice_type TY, " . AppConfig::pre() . "person P " .
                " where R.invoice_id = I.id and I.invoice_top = T.id and TY.invoice_template = ? and T.invoice_type = TY.id and R.invoice_status = 1 and person_id = P.id order by due_date limit 201");

        $prep->bind_params("s", $invoice_template);

        return $prep->execute();
    }

    public function invoices($invoice, $dueDate) {

        if ($invoice) {
            $prep = $this->db->prepare("select firstname, lastname, email, TY.description, TY.invoice_template, R.id,invoice_status, sent_date, deleted_date, person_id, amount, due_date, TY.id as template_id from " . AppConfig::pre() . "invoice_recipient R, " . AppConfig::pre() . "invoice I, " . AppConfig::pre() . "invoice_top T, " . AppConfig::pre() . "invoice_type TY, " . AppConfig::pre() . "person P " .
                    " where R.invoice_id = I.id and I.invoice_top = T.id and T.invoice_type = TY.id and R.invoice_id = ? and person_id = P.id order by due_date limit 201");
            $prep->bind_params("i", $invoice);

        } else if ($dueDate) {
            $ezDate = new eZDate();
            $ezDate->setDate($dueDate);

            $prep = $this->db->prepare("select firstname, lastname, email,  TY.description, TY.invoice_template, R.id,invoice_status, sent_date, deleted_date, person_id, amount, due_date, TY.id as template_id from " . AppConfig::pre() . "invoice_recipient R, " . AppConfig::pre() . "invoice I, " . AppConfig::pre() . "invoice_top T, " . AppConfig::pre() . "invoice_type TY, " . AppConfig::pre() . "person P " .
                    " where R.invoice_id = I.id and I.invoice_top = T.id and T.invoice_type = TY.id and due_date < ? and person_id = P.id order by due_date limit 201");
            $prep->bind_params("s", $ezDate->mySQLDate());
        } else {
            $prep = $this->db->prepare("select firstname, lastname, email,  TY.description, TY.invoice_template, R.id,invoice_status, sent_date, deleted_date, person_id, amount, due_date, TY.id as template_id from " . AppConfig::pre() . "invoice_recipient R, " . AppConfig::pre() . "invoice I, " . AppConfig::pre() . "invoice_top T, " . AppConfig::pre() . "invoice_type TY, " . AppConfig::pre() . "person P " .
                    " where R.invoice_id = I.id and I.invoice_top = T.id and T.invoice_type = TY.id and person_id = P.id order by due_date limit 201");
        }
        return $prep->execute();
    }

    public function search($params) {
        $pre = "select firstname, lastname, email,  TY.description, R.id,invoice_status, sent_date, deleted_date, person_id, amount, due_date, TY.id as template_id from "
                . AppConfig::pre() . "invoice_recipient R, " . AppConfig::pre() . "invoice I, " . AppConfig::pre() . "invoice_top T, "
                . AppConfig::pre() . "invoice_type TY, " . AppConfig::pre() . "person P ";
        $pre .= " where R.invoice_id = I.id and I.invoice_top = T.id and T.invoice_type = TY.id and person_id = P.id ";
        $sql = array();
        $values = array();
        $types = "";

        if ($params["from_date"]) {
            $sql[] = "due_date >= ?";
            $types .= "s";
            $md = new eZDate();
            $md->setDate($params["from_date"]);
            $values[] = $md->mysqlDate();
        }

        if ($params["to_date"]) {
            $sql[] = "due_date <= ?";
            $types .= "s";
            $md = new eZDate();
            $md->setDate($params["to_date"]);
            $values[] = $md->mysqlDate();
        }

        if ($params["due_date"]) {
            $sql[] = "due_date = ?";
            $types .= "s";
            $md = new eZDate();
            $md->setDate($params["due_date"]);
            $values[] = $md->mysqlDate();
        }

        if ($params["status"]) {
            $sql[] = "invoice_status = ?";
            $types .= "i";
            $values[] = $params["status"];
        }

        if ($params["amount"]) {
            $sql[] = "amount = ?";
            $money = Strings::money($_REQUEST["amount"]);
            $types .= "d";
            $values[] = $money;
        }

        if($params["invoice"]) {
            $sql[] = "R.id = ?";
            $types .= "i";

            $invoice = $params["invoice"];

            $parts = explode($invoice, "-");

            if(count($parts) == 2) {
                $invoice = $parts[0];
            }

            $values[] = $invoice;

        }

        if ($params["firstname"] && $params["lastname"]) {
            $sql[] = "P.id IN (select id from " . AppConfig::pre() . "person M where M.firstname like ? and M.lastname like ?)";
            $types .= "ss";
            $values[] = $params["firstname"];
            $values[] = $params["lastname"];
        } else if ($params["firstname"]) {
            $sql[] = "P.id IN (select id from " . AppConfig::pre() . "person M where M.firstname like ?)";
            $types .= "s";
            $values[] = $params["firstname"];
        } else if ($params["lastname"]) {
            $sql[] = "P.id IN (select id from " . AppConfig::pre() . "person M where M.lastname like ?)";
            $types .= "s";
            $values[] = $params["lastname"];
        }


        if (count($values) > 0) {
            $pre .= " and " . implode($sql, " and ");
        }

        $prep = $this->db->prepare($pre . " order by due_date limit 201");

        if (count($values)) {
            $prep->bind_array_params($types, $values);
        }

        return $prep->execute();
    }

    public function changeInvoiceStatus($receiverId, $status) {
        $prep = $this->db->prepare("update " . AppConfig::pre() . "invoice_recipient set invoice_status = ? where id = ?");
        $prep->bind_params("ii", $status, $receiverId);
        $prep->execute();

        return $this->db->affected_rows() == 1;
    }

    public function invoicePaidInTransaction($recipient, $day, $amount, $debetPost) {
        $this->db->begin();
        try {
            $this->invoicePaid($recipient, $day, $amount, $debetPost);
            $this->db->commit();

            return 1;
        } catch (Exception $e) {
            $this->db->rollback();
        }
        return 0;
    }

    private function invoicePaid($recipient, $day, $amount, $debetPost) {
        $prep = $this->db->prepare("select description, amount, credit_post_type, TY.invoice_type from " . AppConfig::pre() . "invoice I, " . AppConfig::pre() . "invoice_recipient IR, " . AppConfig::pre() . "invoice_top T, " . AppConfig::pre() . "invoice_type TY," . AppConfig::pre() . "person P where IR.id = ? and IR.invoice_id = I.id and I.invoice_top = T.id and T.invoice_type = TY.id and P.id = person_id");
        $prep->bind_params("i", $recipient);
        $res = $prep->execute();

        $invoiceInfo = array_shift($res);

        if (!$amount) {
            $accountingAmount = $invoiceInfo["amount"];
        } else {
            $accountingAmount = $amount;
        }


        //TODO add membership registration
        switch ($invoiceInfo["invoice_type"]) {
            case 1:
                //return SEMESTER;
            case 2:
                //return SEMESTER_YOUTH;
            case 3:
                //return YEAR;
            case 4:
                //return YEAR_YOUTH;
            case 5:
                //return OTHER;
        }

        $standard = new AccountStandard($this->db);
        $const = $standard->getValues(array(AccountStandard::CONST_YEAR, AccountStandard::CONST_MONTH, AccountStandard::CONST_SEMESTER));
        $active_year = $const[AccountStandard::CONST_YEAR];
        $active_month = $const[AccountStandard::CONST_MONTH];

        $this->db->begin();

        $line = new AccountLine($this->db);
        $line->setNewLatest($invoiceInfo["description"] . ": " . $invoiceInfo["firstname"] . " " . $invoiceInfo["lastname"],
            $day, $active_year, $active_month);
        $line->store();

        $line->addPostSingleAmount($line->getId(), "1", $debetPost, $amount);
        $line->addPostSingleAmount($line->getId(), "-1", $invoiceInfo["credit_post_type"], $amount);

        $prep = $this->db->prepare("update " . AppConfig::pre() . "invoice_recipient set status = 4 where id = ?");
        $prep->bind_params("i", $recipient);
        $prep->execute();

        $this->db->commit();

    }

}

?>