<?php

class EmailContent {

    private $db;

    function EmailContent($db) {
        $this->db = $db;
    }

    function get($id) {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "email_content where id = ?");
        $prep->bind_params("i", $id);

        return array_shift($prep->execute());
    }

    function save($id, $name, $text, $header) {
        $res = 0;
        if($id) {
            $prep = $this->db->prepare("update " . AppConfig::pre() . "email_content set name = ?, content=? where id = ?");
            $prep->bind_params("ssi", $name, $text, $id);
            $prep->execute();
            $res = $this->db->affected_rows();
        } else {
            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "email_content (name, content, header) values (?, ?, ?)");
            $prep->bind_params("ssi", $name, $text, $header);
            $prep->execute();
            $res = $this->db->insert_id();
        }
        return array("result" => $res);
    }

    function getAll() {
        $prep = $this->db->prepare("select id, name, header from " . AppConfig::pre() . "email_content");
        $data = $prep->execute();

        $headers = array();
        $footers = array();

        foreach($data as $one) {
            if($one["header"]) {
                $headers[] = $one;
            } else {
                $footers[] = $one;
            }
        }

        return array("headers" => $headers, "footers" => $footers);
    }

    function attachFooterHeader($body, $footer, $header) {
        if($header) {
            $prep = $this->db->prepare("select content from " . AppConfig::pre() . "email_content where id = ?");
            $prep->bind_params("i", $header);
            $res = $prep->execute();
            
            $body = $res[0]["content"].$body;
        }

        if($footer) {
            $prep = $this->db->prepare("select content from " . AppConfig::pre() . "email_content where id = ?");
            $prep->bind_params("i", $footer);
            $res = $prep->execute();
            
            $body = $body.$res[0]["content"];
        }

        return $body;
    }

}

?>