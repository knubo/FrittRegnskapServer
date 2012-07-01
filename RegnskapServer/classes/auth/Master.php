<?php class Master {

    public $db;

    function Master($db) {
        if (!$db) {
            $db = new DB();
        }
        $this->db = $db;
    }

    function getOneInstallation($id) {
        $prep = $this->db->prepare("select *,(select completed from install_info II where II.id =I.id) as completed from installations I where id = ?");
        $prep->bind_params("i", $id);


        $insObj = array_shift($prep->execute());

        $prepc = $this->db->prepare("select * from change_request where installation_id = ?");
        $prepc->bind_params("i", $id);

        $insObj["changes"] = $prepc->execute();

        return $insObj;
    }




    function getAllInstallations($sort = "") {
        $prep = $this->db->prepare("select *, (select count(*) FROM change_request where installation_id = I.id) as cr, (select completed from install_info II where II.id =I.id) as completed from installations I $sort");

        return $prep->execute();
    }

    function updateInstall($id, $hostprefix, $beta, $quota, $description, $wikilogin, $portal_status, $portal_title, $archive_limit, $parentdbprefix, $reduced_mode, $parenthostprefix) {
        $prep = $this->db->prepare("select hostprefix from installations where id = ?");
        $prep->bind_params("i", $id);
        $data = array_shift($prep->execute());
        $existingHostPrefix = $data["hostprefix"];

        $prep = $this->db->prepare("update installations set hostprefix=?, beta=?, diskquota=?,description=?,wikilogin=?, portal_status=?,portal_title=?, archive_limit=?, parentdbprefix=?, reduced_mode=?, parenthostprefix=? where id = ?");
        $prep->bind_params("sisssisisisi", $hostprefix, $beta, $quota, $description, $wikilogin, $portal_status, $portal_title, $archive_limit, $parentdbprefix, $reduced_mode, $parenthostprefix, $id);
        $prep->execute();

        $updated = $this->db->affected_rows();


        if(strcmp($existingHostPrefix, $hostprefix) != -1) {
            $installer = new Installer($this->db);
            $dbprefix = $installer->createUniquePrefix($hostprefix);

            $prep = $this->db->prepare("update installations set dbprefix = ? where id = ?");
            $prep->bind_params("si", $dbprefix."_", $id);
            $prep->execute();
            return 1;
        }

        return $updated;

    }

    function doDelete($id, $secret) {
        $data = $this->getOneInstallation($id);

        if ($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        try {
            $this->db->begin();
            $prep = $this->db->prepare("delete from installations where id = ?");
            $prep->bind_params("i", $id);
            $prep->execute();

            $prep = $this->db->prepare("delete from change_request where installation_id = ?");
            $prep->bind_params("i", $id);
            $prep->execute();


            $deleteDB = new DB(0, DB::dbhash($data["hostprefix"]));

            $installer = new Installer($deleteDB);
            $installer->dropTables($data["dbprefix"]);

            $this->db->commit();
            echo "Delete complete for " . $data["hostprefix"];
        } catch (Exception $e) {
            echo "Error occured: $e";
            $this->db->rollback();
        }
    }

    public function doDeletePeople($id, $secret) {
        $data = $this->getOneInstallation($id);

        if ($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        try {
            $this->db->begin();
            $deleteDB = new DB(0, DB::dbhash($data["hostprefix"]));

            $this->doDeletePeopleInt($id, $data, $deleteDB);

            $deleteDB->commit();
            echo "Delete complete for " . $data["hostprefix"];
        } catch (Exception $e) {
            echo "Error occured: $e";
            $this->db->rollback();
            $deleteDB->rollback();
        }
    }

    public function doDeletePeopleInt($id, $data, $deleteDB) {
        $prep = $this->db->prepare("update change_request set executedTime = now() where installation_id = ? and action = 'deletePeopleMembers'");
        $prep->bind_params("i", $id);
        $prep->execute();


        $prefix = $data["dbprefix"];

        $deleteDB->begin();
        $deleteDB->action("delete from " . $prefix . "year_membership");
        $deleteDB->action("delete from " . $prefix . "course_membership");
        $deleteDB->action("delete from " . $prefix . "train_membership");
        $deleteDB->action("delete from " . $prefix . "person where id > 1");
        $deleteDB->action("delete from " . $prefix . "person_backup");
        $deleteDB->action("update " . $prefix . "post set person = null");
    }

    public function doDeleteAccounting($id, $secret) {
        $data = $this->getOneInstallation($id);

        if ($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        try {
            $this->db->begin();
            $deleteDB = new DB(0, DB::dbhash($data["hostprefix"]));

            $this->doDeleteAccountingInt($id, $data, $deleteDB);

            $deleteDB->commit();
            echo "Delete complete for " . $data["hostprefix"];
        } catch (Exception $e) {
            echo "Error occured: $e";
            $this->db->rollback();
            $deleteDB->rollback();
        }
    }

    public function doDeleteAccountingInt($id, $data, $deleteDB) {
        $prep = $this->db->prepare("update change_request set executedTime = now() where installation_id = ? and action = 'deleteAccountingData'");
        $prep->bind_params("i", $id);
        $prep->execute();


        $prefix = $data["dbprefix"];

        $deleteDB->begin();
        $deleteDB->action("delete from " . $prefix . "telling");
        $deleteDB->action("delete from " . $prefix . "post");
        $deleteDB->action("delete from " . $prefix . "line");
        $deleteDB->action("delete from " . $prefix . "year_membership");
        $deleteDB->action("delete from " . $prefix . "course_membership");
        $deleteDB->action("delete from " . $prefix . "train_membership");
        $deleteDB->action("delete from " . $prefix . "kid");
        $deleteDB->action("delete from " . $prefix . "semester");
        $deleteDB->action("update " . $prefix . "standard set value = '0' where id = 'FIRST_TIME'");
    }

    public function doDeletePeopleAndAccounting($id, $secret) {
          $data = $this->getOneInstallation($id);

        if ($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        try {
            $this->db->begin();
            $deleteDB = new DB(0, DB::dbhash($data["hostprefix"]));

            $this->doDeleteAccountingInt($id, $data, $deleteDB);
            $this->doDeletePeopleInt($id, $data, $deleteDB);

            $deleteDB->commit();
            echo "Delete complete for " . $data["hostprefix"];

        } catch (Exception $e) {
            echo "Error occured: $e";
            $this->db->rollback();
            $deleteDB->rollback();
        }
    }


    function deleteForm($id, $secret, $desc, $nextAction) {
        $data = $this->getOneInstallation($id);

        if ($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        $newSecret = $this->updateSecret($id);

        echo "<html><body><form action=\"installs.php\">" .
             "Perform delete ($desc) for id:$id, hostprefix=" . $data["hostprefix"] . "?<br/>" .
             "<input type=\"hidden\" name=\"action\" value=\"$nextAction\"/>" .
             "<input type=\"hidden\" name=\"secret\" value=\"$newSecret\"/>" .
             "<input type=\"hidden\" name=\"id\" value=\"$id\"/>" .
             "<input type=\"submit\" value=\"Confirm delete\"/>" .
             "</form></body></html>";
    }

    function updateSecret($id) {
        $secret = Strings::createSecret();

        $prep = $this->db->prepare("update installations set secret = ? where id = ?");
        $prep->bind_params("si", $secret, $id);
        $prep->execute();

        return $secret;
    }

    function deletePeopleAndAccountingRequest($id) {
        $secret = $this->updateSecret($id);

        $data = $this->getOneInstallation($id);

        $subject = "Delete people and accoutning request for Fritt Regnskap " . $data["hostprefix"];
        $body = "Delete people and accounting request queued for " . $data["hostprefix"] . " description: " . $data["description"] . "\n" . "Confirm by using link: " .
                "http://master.frittregnskap.no/RegnskapServer/services/admin/installs.php?action=deletePeopleAndAccounting&id=$id&secret=$secret";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, "admin@frittregnskap.no", $body, "admin@frittregnskap.no", null);

    }

    function deletePeopleRequest($id) {
        $secret = $this->updateSecret($id);

        $data = $this->getOneInstallation($id);

        $subject = "Delete people request for Fritt Regnskap " . $data["hostprefix"];
        $body = "Delete people request queued for " . $data["hostprefix"] . " description: " . $data["description"] . "\n" . "Confirm by using link: " .
                "http://master.frittregnskap.no/RegnskapServer/services/admin/installs.php?action=deletePeople&id=$id&secret=$secret";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, "admin@frittregnskap.no", $body, "admin@frittregnskap.no", null);
    }

    function deleteAccountingRequest($id) {
        $secret = $this->updateSecret($id);

        $data = $this->getOneInstallation($id);

        $subject = "Delete accounting request for Fritt Regnskap " . $data["hostprefix"];
        $body = "Delete accounting request queued for " . $data["hostprefix"] . " description: " . $data["description"] . "\n" . "Confirm by using link: " .
                "http://master.frittregnskap.no/RegnskapServer/services/admin/installs.php?action=deleteAccounting&id=$id&secret=$secret";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, "admin@frittregnskap.no", $body, "admin@frittregnskap.no", null);

    }

    function deleteRequest($id) {
        $secret = $this->updateSecret($id);

        $data = $this->getOneInstallation($id);

        $subject = "Delete request for Fritt Regnskap " . $data["hostprefix"];
        $body = "Delete request queued for " . $data["hostprefix"] . " description: " . $data["description"] . "\n" . "Confirm by using link: " .
                "http://master.frittregnskap.no/RegnskapServer/services/admin/installs.php?action=delete&id=$id&secret=$secret";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, "admin@frittregnskap.no", $body, "admin@frittregnskap.no", null);

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
        if (!$this->db) {
            $this->db = new DB(0, DB::MASTER_DB);
        }
        $prep = $this->db->prepare("select * from installations where hostprefix = ?");

        $host = $_SERVER["SERVER_NAME"];

        $split = explode(".", $host);

        if (strlen($split[0]) < 2 || $split[0] == "localhost") {
            $split[0] = "php5";
        }

        $prep->bind_params("s", $split[0]);

        $res = $prep->execute();

        if (count($res) > 0) {
            return $res[0];
        }

        /* Default to BSC as of now */
        return array("dbprefix" => "regn_", "default" => true, "diskquota" => 42);
    }


    function sendPortalLetter($id) {

        //$data = $this->getOneInstallation($id);
        $prep = $this->db->prepare("select dbprefix,hostprefix from installations where id = ?");
        $prep->bind_params("i", $id);

        $res = $prep->execute();

        if (count($res) == 0) {
            die("No install found for " + $id);
        }

        $hostprefix = $res[0]["hostprefix"];

        $dbUser = new DB(0, DB::dbhash($hostprefix));

        $prefix = $res[0]["dbprefix"];
        $prep = $dbUser->prepare("select email from " . $prefix . "user U, " . $prefix . "person P where U.person=P.id ");

        $res = $prep->execute();

        $emails = array();
        foreach ($res as $one) {
            if ($one["email"] && strlen($one["email"]) > 0)
                $emails[] = $one["email"];
        }

        $subject = "Medlemsportalen er aktivert for $hostprefix.frittregnskap.no.";
        $body = "Medlemsportalen er blitt aktivert. Den er tilgjengelig her:\n\n" .
                "  http://$hostprefix.frittregnskap.no/portal\n" .
                "\nMvh\nAdministrajonen for Fritt Regnskap\n";

        $emailer = new Emailer();
        $email = implode(",", $emails);
        $emailer->sendEmail($subject, $email, $body, "admin@frittregnskap.no", null, 0, 0, "admin@frittregnskap.no");

        echo json_encode(array("result" => ok, "receivers" => $email));


    }

    function sendWelcomeLetter($id) {
        $data = $this->getOneInstallation($id);
        $prep = $this->db->prepare("select email from " . AppConfig::WIKKA_PREFIX . "users where name like ?");
        $prep->bind_params("s", $data["wikilogin"]);

        $res = $prep->execute();

        if (count($res) == 0) {
            die("No email found for " + $id);
        }

        $email = $res[0]["email"];

        if (!$email) {
            die("No email found in:" . json_encode($res));
        }

        $subject = "Ditt regnskapssystem hos Fritt Regnskap er klart til bruk";
        $body = "Velkommen til Fritt Regnskap!\n\nRegnskapsystemet ditt er klart til bruk via addressen:\n\nhttp://" . $data["hostprefix"] . ".frittregnskap.no/prg/AccountingGWT.html\n" .
                "\nMvh\nAdministrajonen for Fritt Regnskap\n";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, $email, $body, "admin@frittregnskap.no", null, 0, 0, "admin@frittregnskap.no");

        echo json_encode(array("result" => ok));
    }

    function prepareAndAddSecret($wikilogin, $domain) {

        $prep = $this->db->prepare("select * from " . AppConfig::WIKKA_PREFIX . "users where name = ?");
        $prep->bind_params("s", $wikilogin);
        $res = $prep->execute();

        if (count($res) == 0) {
            header("HTTP/1.0 513 Validation Error");
            die(json_encode(array("wikilogin")));
        }

        $prep = $this->db->prepare("select * from installations where hostprefix = ?");
        $prep->bind_params("s", $domain);
        $res = $prep->execute();

        if (count($res) != 0) {
            header("HTTP/1.0 513 Validation Error");
            die(json_encode(array("domain")));
        }

        $secret = "";
        for ($i = 0; $i < 80; $i++) {
            $secret .= chr(mt_rand(97, 122));
        }

        $prep = $this->db->prepare("insert into to_install (wikilogin, secret) values (?,?)");
        $prep->bind_params("ss", $wikilogin, $secret);
        $prep->execute();

        return $secret;
    }

    public function getInstallDetails($id) {
        $prep = $this->db->prepare("select * from install_info where id = ?");
        $prep->bind_params("i", $id);

        return array_shift($prep->execute());
    }

    public function updateInstallDetail($id, $data) {
        $prep = $this->db->prepare("update install_info set username=?, password=?, clubname=?, contact=?, email=?, address=?, postnmb=?, city=?,phone=? where id = ?");
        $prep->bind_params("sssssssssi",  $data->username, $data->password, $data->clubname, $data->contact, $data->email, $data->address, $data->postnmb, $data->city,$data->phone, $id);
        $prep->execute();
    }


}

?>
