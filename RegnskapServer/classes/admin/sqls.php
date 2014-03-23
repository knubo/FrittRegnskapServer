<?php

class SQLS {
    private $db;

    function SQLS($db) {
        $this->db = $db;
    }

    function getAll() {
        $prep = $this->db->prepare("select * from sqllist");
        return $prep->execute();
    }

    function getOneSQL($id) {
        $prep = $this->db->prepare("select * from sqllist where id = ?");
        $prep->bind_params("i", $id);
        $result = $prep->execute();
        return array_shift($result);

    }

    function betaComplete($id) {
        $prep = $this->db->prepare("update sqllist set runinbeta = 1 where id = ?");
        $prep->bind_params("i", $id);
        $prep->execute();
    }

    function otherComplete($id) {
        $prep = $this->db->prepare("update sqllist set runinother = 1 where id = ?");
        $prep->bind_params("i", $id);
        $prep->execute();
    }

    function delete($id) {
        $prep = $this->db->prepare("delete from sqllist where id = ?");
        $prep->bind_params("i", $id);
        $prep->execute();
    }

    function addSQL($sql) {
        $this->db->begin();
        $prep = $this->db->prepare("insert into sqllist (sqltorun, added, verified, secret, runinbeta, runinother) values (?, now(), 0, ?, 0, 0)");

        $secret = Strings::createSecret();

        $prep->bind_params("ss", $sql, $secret);
        $prep->execute();

        $id = $this->db->insert_id();


        $this->db->commit();

        $subject = "New SQL for Fritt Regnskap ";
        $body = "A new SQL has been queued for verification:\n$sql\nConfirm by using link: " .
                "http://master.frittregnskap.no/RegnskapServer/services/admin/admin_sql.php?action=verify&id=$id&secret=$secret";

        $emailer = new Emailer();
        $emailer->sendEmail($subject, "admin@frittregnskap.no", $body, "admin@frittregnskap.no", null);
    }

    function updateSecret($id) {
        $secret = Strings::createSecret();

        $prep = $this->db->prepare("update sqllist set secret = ? where id = ?");
        $prep->bind_params("si", $secret, $id);
        $prep->execute();

        return $secret;
    }

    function confirmVerify($id, $secret) {
        $data = $this->getOneSQL($id);

        if ($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        $prep = $this->db->prepare("update sqllist set verified = 1 where id = ?");
        $prep->bind_params("i", $id);
        $prep->execute();
        echo "Verified!";
    }


    function verifyForm($id, $secret) {
        $data = $this->getOneSQL($id);

        if ($data["secret"] != $secret) {
            die("Secret mismatch!");
        }

        $newSecret = $this->updateSecret($id);

        echo "<html><body><form action=\"admin_sql.php\">" .
                "Verify SQL id:$id, sql=" . $data["sqltorun"] . "?<br/>" .
                "<input type=\"hidden\" name=\"action\" value=\"confirmVerify\"/>" .
                "<input type=\"hidden\" name=\"secret\" value=\"$newSecret\"/>" .
                "<input type=\"hidden\" name=\"id\" value=\"$id\"/>" .
                "<input type=\"submit\" value=\"Verify SQL\"/>" .
                "</form></body></html>";
    }

    function run($id, $install) {
        $sql = $this->getOneSQL($id);
        if (!$sql["verified"]) {
            header("HTTP/1.0 515 DB error");
            die("The given sql id is not verified!");
        }

        $dbprefix = $install["dbprefix"];
        $hostprefix = $install["hostprefix"];

        $targetDB = new DB(0, DB::dbhash($hostprefix));

        $replaced_sql = preg_replace("/XXX\_?/", $dbprefix, $sql["sqltorun"]);

        $replaced_sql = preg_replace("/\\n/", " ", $replaced_sql);

        $sqlToRun = explode(");", $replaced_sql);

        foreach ($sqlToRun as $sql) {
            try {
                $sql = trim($sql);

                if (strlen($sql) == 0) {
                    continue;
                }

                $sql = $sql . ");";

                $res = array();
                /* The bsc database runs on an old mysql instance, hence the "fix". */
                if ($dbprefix == "regn_") {
                    $targetDB->action($sql);
                } else {
                    $prep = $targetDB->prepare($sql);
                    $res["data"] = $prep->execute();
                }

                $res["rows"] = $targetDB->affected_rows();

                $iPrep = $this->db->prepare("update installations set sqlIdToRun = null where id = ?");
                $iPrep->bind_params("i", $install["id"]);
                $iPrep->execute();
            } catch (exception $e) {
                throw new Exception("SQL was $sql", $e);
            }
        }

        return $res;
    }
}

?>