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
        if ($id) {
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

        foreach ($data as $one) {
            if ($one["header"]) {
                $headers[] = $one;
            } else {
                $footers[] = $one;
            }
        }

        return array("headers" => $headers, "footers" => $footers);
    }

    function attachFooterHeader($body, $footer, $header) {
        if ($header) {
            $prep = $this->db->prepare("select content from " . AppConfig::pre() . "email_content where id = ?");
            $prep->bind_params("i", $header);
            $res = $prep->execute();

            $body = $res[0]["content"] . $body;
        }

        if ($footer) {
            $prep = $this->db->prepare("select content from " . AppConfig::pre() . "email_content where id = ?");
            $prep->bind_params("i", $footer);
            $res = $prep->execute();

            $body = $body . $res[0]["content"];
        }

        return $body;
    }

    function makePlainText($html) {
        $html = preg_replace("/<h1>(.+?)<\/h1>/", "==========\n $1\n==========\n", $html);
        $html = preg_replace("/<h2>(.+?)<\/h2>/", " $1\n=====\n", $html);
        $html = preg_replace("/<h3>(.+?)<\/h3>/", "$1\n-----\n", $html);
        $html = preg_replace("/<h4>(.+?)<\/h4>/", "_$1_\n\n", $html);
        $html = preg_replace("/<a href=.(.+?).>(.+?)<\/a>/", "link ($2): $1", $html);
        $html = preg_replace("/<hr>/", "\n--------------------------------------------------------------------\n", $html);
        $html = preg_replace("/<p>/", "\n", $html);
        $html = preg_replace("/<.+?>/", "", $html);
        return $html;
    }

    function makeHTMLFromWiki($body) {
        $lines = explode("\n", $body);
        reset($lines);

        $res = "";
        foreach ($lines as $one) {
            if (strncasecmp("h1.", $one, 3) == 0) {
                $res .= "<h1>" . trim(substr($one, 3)) . "</h1>\n";
            } else if (strncasecmp("h2.", $one, 3) == 0) {
                $res .= "<h2>" . trim(substr($one, 3)) . "</h2>\n";
            } else if (strncasecmp("h3.", $one, 3) == 0) {
                $res .= "<h3>" . trim(substr($one, 3)) . "</h3>\n";
            } else if (strncasecmp("h4.", $one, 3) == 0) {
                $res .= "<h4>" . trim(substr($one, 3)) . "</h4>\n";
            } else if (strncasecmp("hr.", $one, 3) == 0) {
                $res .= "<hr>\n";
            } else if (strlen($one) == 0) {
                $res .= "<p>\n";
            } else {
                $one = preg_replace("/\*(.+?)\*/", "<strong>$1</strong>", $one);
                $one = preg_replace("/\_(.+?)\_/", "<u>$1</u>", $one);
                $one = preg_replace("/\|(.+?)\|(.+?)\|/", "<a href=\"$1\">$2</a>", $one);
                $res .= "$one\n";
            }
        }

        return "<html><body>$res</body></html>";
    }

    function fillInUnsubscribeURL($body, $secret, $personId) {
        $protocol = $_SERVER["https"] ? "https://" : "http://";
        $url = $protocol . $_SERVER["SERVER_NAME"] . AppConfig::ABSOLUTE_URL_TO_SERVICES . "newsletter/unsubscribe.php?secret=" . $personId . $secret;
        return preg_replace("/\{unsubscribeurl\}/", $url, $body);
    }

    public function replaceCommonVariables($body) {
        $now = new eZDate();

        $body = preg_replace("/\{day\}/", $now->day(), $body);
        $body = preg_replace("/\{month\}/", $now->month(), $body);
        $body = preg_replace("/\{year\}/", $now->year(), $body);
        $body = preg_replace("/\{week\}/", $now->week(), $body);


        return $body;
    }

}

?>