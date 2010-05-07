<?php

class Installer {

    private $db;


    function Installer($db) {
        $this->db = $db;
    }

    function dropTables($prefix) {
        $prep = $this->db->prepare("show tables like '$prefix%'");

        $res = $prep->execute();

        foreach($res as $one) {
            foreach(array_values($one) as $st) {
                $this->db->action("drop table $st");
            }
        }
    }

    function createTables($prefix) {
        $dbschema = file_get_contents("../../conf/dbschema.sql");

        $this->execute($dbschema, $prefix);
    }

    function createIndexes($prefix) {
        $indexes = file_get_contents("../../conf/indexes.sql");

        $this->execute($indexes, $prefix);

    }

    function addAccountPlan($prefix) {
        $posts = file_get_contents("../../conf/posts.sql");

        $this->execute($posts, $prefix);
        
        $this->db->action("update ".$prefix."_post_type set in_use = 1");
    }

    function execute($sqls, $prefix) {
        $replaced = preg_replace("/XXX/",$prefix,$sqls);

        $statements = explode(";", $replaced);

        foreach ($statements as $one) {
            if($one && strlen(chop($one)) > 0) {
                $this->db->action($one);
            }
        }

    }

    function createUniquePrefix($hostprefix, $retry = 0) {
        $try = substr($hostprefix, 0, 3).substr($hostprefix, -3)."_";

        if($retry > 0) {
            $try = substr($try, 0, -1)."$retry_";
            $retry++;
        }

        $prep = $this->db->prepare("select * from installations where dbprefix = ?");
        $prep->bind_params("s", $try);
        $res = $prep->execute();

        if(count($res) == 0) {
            return 0;
        }

        return $this->createUniquePrefix($hostprefix, $retry + 1);
    }

    function sendEmailRequestDomain($wikilogin, $address, $email, $contact, $clubname, $domainname) {
        $subject = urldecode("Domenerequest Fritt Regnskap: $domainname");
        $body = urldecode("Wikilogin: $wikilogin\nAdresse:$address\nEmail:$email\nContact:$contact\nClubname:$clubname\nDomainname:$domainname\n");
        
        $email = "knutbo+fr@ifi.uio.no";
        $emailer = new Emailer();
        
        $status = $emailer->sendEmail($subject, $email, $body, $email, 0);

    }
    
    function addStandardData($prefix) {
        $prefix = $prefix."_";
            
        $ezDate = new eZDate();
        
        $year = $ezDate->year();
        
        for($i = 0; $i < 10; $i++) {
            $prep = $this->db->prepare("insert into ".$prefix."semester (description, year, fall) values (?,?,?)");

            $desc = $i % 2 == 0 ? "V�r $year" : "H�st $year";

            if($i > 0 && $i % 2 == 0) {
                $year++;
            }
            
            $prep->bind_params("sii", $desc, $year, $i % 2);
            $prep->execute();
        }
        
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('STD_SEMESTER','1')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('STD_MONTH','1')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('STD_YEAR','$year')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('BDG_YEAR_POST','3920')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('BDG_COURSE_POST','3925')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('BDG_TRAIN_POST','3926')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('BDG_YOUTH_POST','3927')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('END_MONTH_POST','9000')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('END_YEAR_POST','8800')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('FORDRINGER_POSTS','1370,1380,1390,1570')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('REGI_MEMB_POSTS','1920,1905')");
        $prep = $this->db->action("insert into ".$prefix."standard (id,value) values ('END_MONTH_TRPOSTS','1904,1905,1906,1921')");
        
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
        
        if(count($res) == 0) {
            $prep = $this->db->prepare("insert into installations (dbprefix, hostprefix, description, diskquota, wikilogin) values ('master_', 'www','Master',0,?)");
            $prep->bind_params("s", $wikilogin);
            $prep->execute();
            
        }
        
    }
}




?>