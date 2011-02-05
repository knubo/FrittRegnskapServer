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

    function updateInstall($id, $hostprefix, $beta, $quota, $description, $wikilogin,$portal_status,$portal_title,$archive_limit) {
        $prep = $this->db->prepare("update installations set hostprefix=?, beta=?, diskquota=?,description=?,wikilogin=?, portal_status=?,portal_title=?, archive_limit=? where id = ?");
        $prep->bind_params("sisssisii", $hostprefix, $beta, $quota, $description, $wikilogin, $portal_status, $portal_title, $archive_limit, $id);
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


    function sendPortalLetter($id) {

        $data = $this->getOneInstallation($id);
        $prep = $this->db->prepare("select dbprefix,hostprefix from installations where id = ?");
        $prep->bind_params("i", $id);

        $res = $prep->execute();

        if(count($res) == 0) {
            die("No install found for "+$id);
        }

        $hostprefix = $res[0]["hostprefix"];

        $dbUser = new DB(0, DB::dbhash($hostprefix));

        $prefix = $res[0]["dbprefix"];
        $prep = $dbUser->prepare("select email from ".$prefix."user U, ".$prefix."person P where U.person=P.id ");

        $res = $prep->execute();

        $emails = array();
        foreach($res as $one) {
            if($one["email"] && strlen($one["email"]) > 0) 
            $emails[] = $one["email"];
        }

        $subject = "Medlemsportalen er aktivert for $hostprefix.frittregnskap.no.";
        $body="Medlemsportalen er blitt aktivert. Den er tilgjengelig her:\n\n".
             "  http://$hostprefix.frittregnskap.no/portal\n".
             "\nMvh\nAdministrajonen for Fritt Regnskap\n";

        $emailer = new Emailer();
        $email = implode(",", $emails);
        $emailer->sendEmail($subject, $email,$body,"admin@frittregnskap.no", null,0,0, "admin@frittregnskap.no");

        echo json_encode(array("result"=> ok, "receivers"=>$email));


    }

    function sendWelcomeLetter($id) {
        $data = $this->getOneInstallation($id);
        $prep = $this->db->prepare("select email from ".AppConfig::WIKKA_PREFIX."users where name like ?");
        $prep->bind_params("s", $data["wikilogin"]);

        $res = $prep->execute();

        if(count($res) == 0) {
            die("No email found for "+$id);
        }

        $email = $res[0]["email"];

        if(!$email) {
            die("No email found in:".json_encode($res));
        }

        $subject = "Ditt regnskapssystem hos Fritt Regnskap er klart til bruk";
        $body="Velkommen til Fritt Regnskap!\n\nRegnskapsystemet ditt er klart til bruk via addressen:\n\nhttp://".$data["hostprefix"].".frittregnskap.no/prg/AccountingGWT.html\n".
                "\nMvh\nAdministrajonen for Fritt Regnskap\n";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, $email,$body,"admin@frittregnskap.no", null,0,0, "admin@frittregnskap.no");

        echo json_encode(array("result"=> ok));
    }


}

?>
