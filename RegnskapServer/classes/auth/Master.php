<?php class Master {

    public $db;

    function Master($db) {
        $this->db = $db;
    }

    function getOneInstallation($id) {
        $prep = $this->db->prepare("select * from installations where id = ?");
        $prep->bind_params("i", $id);
        return array_shift($prep->execute());
    }

    function getAllInstallations($sort = "") {
        $prep = $this->db->prepare("select * from installations $sort");

        return $prep->execute();
    }

    function updateInstall($id, $hostprefix, $beta, $quota, $description, $wikilogin) {
        $prep = $this->db->prepare("update installations set hostprefix=?, beta=?, diskquota=?,description=?,wikilogin=? where id = ?");
        $prep->bind_params("sisssi", $hostprefix, $beta, $quota, $description, $wikilogin, $id);
        $prep->execute();

        return $this->db->affected_rows();
         
    }

    function doDelete($id, $secret) {
        $data = $this->getOneInstallation($id);

        if($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        try {
            $this->db->begin();
            $prep = $this->db->prepare("delete from installations where id = ?");
            $prep->bind_params("i", $id);
            $prep->execute();

            $deleteDB = new DB(0, DB::dbhash($data["hostprefix"]));

            $installer = new Installer($deleteDB);
            $installer->dropTables($data["dbprefix"]);

            $this->db->commit();
            echo "Delete complete for ".$data["hostprefix"];
        } catch(Exception $e) {
            echo "Error occured: $e";
            $this->db->rollback();
        }

    }

    function deleteForm($id, $secret) {
        $data = $this->getOneInstallation($id);

        if($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        $newSecret = $this->updateSecret($id);

        echo "<html><body><form action=\"installs.php\">".
            "Perform delete for id:$id, hostprefix=".$data["hostprefix"]."?<br/>".
            "<input type=\"hidden\" name=\"action\" value=\"doDelete\"/>".
            "<input type=\"hidden\" name=\"secret\" value=\"$newSecret\"/>".
            "<input type=\"hidden\" name=\"id\" value=\"$id\"/>".
            "<input type=\"submit\" value=\"Confirm delete\"/>".
            "</form></body></html>";
    }

    function updateSecret($id) {
        $secret = Strings::createSecret();

        $prep = $this->db->prepare("update installations set secret = ? where id = ?");
        $prep->bind_params("si", $secret, $id);
        $prep->execute();

        return $secret;
    }

    function deleteRequest($id) {
        $secret = $this->updateSecret($id);


        $data = $this->getOneInstallation($id);

        $subject = "Delete request for Fritt Regnskap ".$data["hostprefix"];
        $body="Delete request queued for ".$data["hostprefix"]." description: ".$data["description"]."\n"."Confirm by using link: ".
                "http://master.frittregnskap.no/RegnskapServer/services/admin/installs.php?action=delete&id=$id&secret=$secret";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, "admin@frittregnskap.no",$body,"admin@frittregnskap.no", null);

    }

    function update_portal_status($newstatus) {
        $install = $this->get_master_record();

        $prep = $this->db->prepare("update installations set portal_status=? where id=?");
        $prep->bind_params("ii", $newstatus, $install["id"]);
         
        $prep->execute();
        
    }

    function update_portal_info($title) {
        $install = $this->get_master_record();

        $prep = $this->db->prepare("update installations set portal_title=? where id=?");
        $prep->bind_params("si", $title, $install["id"]);
         
        $prep->execute();
    }

    function get_master_record() {
        /* Do not understand this bug, why is this needed?... */
        if(!$this->db) {
            $this->db = new DB(0, DB::MASTER_DB);
        }
        $prep = $this->db->prepare("select * from installations where hostprefix = ?");

        $host = $_SERVER["SERVER_NAME"];

        $split = explode(".",$host);

        if(strlen($split[0]) < 2 || $split[0] == "localhost") {
            $split[0] = "php5";
        }

        $prep->bind_params("s", $split[0]);

        $res = $prep->execute();

        if(count($res) > 0) {
            return $res[0];
        }

        /* Default to BSC as of now */
        return array("dbprefix" => "regn_", "default"=>true, "diskquota"=>42);
    }


}

?>
