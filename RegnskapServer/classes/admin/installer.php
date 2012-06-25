<?php

class Installer {

    private $db;


    function Installer($db) {
        if (!$db) {
            $db = new DB();
        }
        $this->db = $db;

    }

    function dropTables($prefix) {
        $prep = $this->db->prepare("show tables like '$prefix%'");

        $res = $prep->execute();

        foreach ($res as $one) {
            foreach (array_values($one) as $st) {
                $this->db->action("drop table $st");
            }
        }
    }


    function createTables($prefix) {
        $dbschema = Strings::file_get_contents_utf8("../../conf/dbschema.sql");

        $this->execute($dbschema, $prefix);
    }

    public function createBackupTables($prefix) {
        $dbschema = Strings::file_get_contents_utf8("../../conf/dbschema.sql");

        $dbschema = preg_replace("/XXX_(.+?)\s*\(/", "XXX_$1_backup (", $dbschema);

        $this->execute($dbschema, $prefix);
    }


    function createIndexes($prefix) {
        $indexes = Strings::file_get_contents_utf8("../../conf/indexes.sql");

        $this->execute($indexes, $prefix);

    }

    function addAccountPlan($prefix) {
        $posts = Strings::file_get_contents_utf8("../../conf/posts.sql");

        $this->execute($posts, $prefix);

        $this->db->action("update " . $prefix . "_post_type set in_use = 1");
    }

    function execute($sqls, $prefix) {
        $replaced = preg_replace("/XXX/", $prefix, $sqls);

        $statements = explode(";", $replaced);

        foreach ($statements as $one) {
            if ($one && strlen(chop($one)) > 0) {
                $this->db->action($one);
            }
        }

    }

    function createUniquePrefix($hostprefix, $retry = 0) {
        $try = substr($hostprefix, 0, 3) . substr($hostprefix, -3);

        if ($retry > 0) {
            $try = substr($try, 0, -1) . "$retry";
            $retry++;
        }

        $prep = $this->db->prepare("select * from installations where dbprefix = ?");
        $prep->bind_params("s", $try);
        $res = $prep->execute();

        if (count($res) == 0) {
            return $try;
        }

        return $this->createUniquePrefix($hostprefix, $retry + 1);
    }

    function sendEmailRequestDomain($wikilogin, $address, $email, $contact, $clubname, $domainname) {
        $subject = urldecode("Domenerequest Fritt Regnskap: $domainname");
        $body = urldecode("Wikilogin: $wikilogin\nAdresse:$address\nEmail:$email\nContact:$contact\nClubname:$clubname\nDomainname:$domainname\n");

        $email = "admin@frittregnskap.no";
        $emailer = new Emailer();

        $status = $emailer->sendEmail($subject, $email, $body, $email, 0);

    }

    function addStandardData($prefix) {
        $prefix = $prefix . "_";


        $this->db->action("insert into " . $prefix . "standard (id,value) values ('STD_SEMESTER','1')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('STD_MONTH','1')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('STD_YEAR','$year')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('BDG_YEAR_POST','3920')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('BDG_COURSE_POST','3925')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('BDG_TRAIN_POST','3926')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('BDG_YOUTH_POST','3927')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('END_MONTH_POST','9000')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('END_YEAR_POST','2050')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('FIRST_IB_POST','8960')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('FORDRINGER_POSTS','1370,1380,1390,1570')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('REGI_MEMB_POSTS','1920,1900')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('END_MONTH_TRPOSTS','1900,1920')");
        $this->db->action("insert into " . $prefix . "standard (id,value) values ('KID_BANK_ACCOUNT','1920')");

    }

    function addAdminUser($admin, $password) {
        $prep = $this->db->prepare("insert into master_person (firstname, lastname) values (?,?)");
        $prep->bind_params("ss", "Admin (edit)", "Admin (edit)");
        $prep->execute();

        $user = new User(0);
        $crypted = crypt($password, $user->makesalt());

        $prep = $this->db->prepare("insert into master_user (username, pass, person, readonly, reducedwrite, project_required) values (?,?,1,0,0,0)");
        $prep->bind_params("ss", $admin, $crypted);
        $prep->execute();
    }

    function addMasterToInstallations($wikilogin) {
        $prep = $this->db->prepare("select * from installations where dbprefix = 'master'");
        $res = $prep->execute();

        if (count($res) == 0) {
            $prep = $this->db->prepare("insert into installations (dbprefix, hostprefix, description, diskquota, wikilogin) values ('master_', 'master','Master',0,?)");
            $prep->bind_params("s", $wikilogin);
            $prep->execute();

        }

    }

    function validate($parameters) {
        $required = array("superuser", "password", "domainname", "clubname", "contact", "email", "address", "city", "zipcode");

        $bad = array();

        foreach ($required as $one) {
            if (!array_key_exists($one, $_REQUEST) || strlen(trim($_REQUEST[$one])) == 0) {
                $bad[] = $one;
            }
        }

        $prep = $this->db->prepare("select * from installations where hostprefix = ?");
        $prep->bind_params("s", $_REQUEST["domainname"]);

        $res = $prep->execute();

        if (count($res)) {
            $bad[] = "domainname";
        }


        if (count($bad) > 0) {
            header("HTTP/1.0 513 Validation Error");
            die(json_encode($bad));
        }

    }

    public function completeInstall($id) {
        $prep = $this->db->prepare("select * from installations I,install_info II where I.id = ? and I.id = II.id");
        $prep->bind_params("i", $id);
        $data = array_shift($prep->execute());

        $domainname = $data["hostprefix"];
        $dbprefix = $data["dbprefix"];
        $clubname = $data["clubname"];
        $superuser = $data["username"];
        $contact = $data["contact"];
        $email = $data["email"];
        $address = $data["address"];
        $zipcode = $data["postnmb"];
        $city = $data["city"];
        $phone = $data["phone"];
        $password = $data["password"];
        $db = $this->db;

        $dbUser = new DB(0, DB::dbhash($domainname));
        $installer = new Installer($dbUser);

        $installer->createTables($dbprefix);
        $installer->createIndexes($dbprefix);
        $installer->addAccountPlan($dbprefix);
        $installer->addStandardData($dbprefix);


        $prep = $db->prepare("insert into master_person  (firstname, lastname, email, address,postnmb, city,phone) values (?,?,?,?,?,?,?)");
        $prep->bind_params("sssssss", "Klubb:$clubname", $contact, $email, $address, $zipcode, $city, $phone);
        $prep->execute();

        $prep = $dbUser->prepare("insert into " . $dbprefix . "_person  (firstname, lastname, email, address,postnmb, city,phone) values (?,?,?,?,?,?,?)");
        $prep->bind_params("sssssss", "Superbruker", $contact, $email, $address, $zipcode, $city, $phone);
        $prep->execute();

        $prep = $dbUser->prepare("insert into " . $dbprefix . "_user (username, pass, person, readonly, reducedwrite, project_required, see_secret) values (?,?,1,0,0,0, 1)");
        $prep->bind_params("ss", $superuser, $password);
        $prep->execute();

        $prep = $db->prepare("update install_info set completed = 1 where id = ?");
        $prep->bind_params("i",$id);
        $prep->execute();

    }

}


?>