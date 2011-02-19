<?php
class AccountPerson {
    public $Id;
    public $FirstName;
    public $LastName;
    public $IsEmployee;
    public $Address;
    public $PostNmb;
    public $City;
    public $Country;
    public $Phone;
    public $Cellphone;
    public $Email;
    /* Here kept as dd.mm.yyyy */
    public $Birthdate;
    public $Newsletter;
    public $Hidden;
    public $Gender;
    public $Secretaddress;
    public $Comment;

    /* Populated from outside */
    public $Memberships;
    public $BirthdateRequired;

    /* Only for querying - not in result set */
    private $User;
    private $db;
    private $dbPrefix;

    function AccountPerson($db, $dbP = 0) {
        $this->db = $db;

        if(!$dbP) {
            $this->dbPrefix = AppConfig::pre();
        } else {
            $this->dbPrefix = $dbP;
        }
        
    }
    function setId($id) {
        $this->Id = $id;
    }

    function setUser($user) {
        $this->User = $user;
    }

    function setFirstname($firstname) {
        $this->FirstName = $firstname;
    }
    function setLastname($lastname) {
        $this->LastName = $lastname;
    }
    function setIsEmployee($isEmployee) {
        $this->IsEmployee = $isEmployee;
    }

    function setAddress($address) {
        $this->Address = $address;
    }
    function setCity($city) {
        $this->City = $city;
    }

    function setCountry($country) {
        $this->Country = $country;
    }
    function setPostnmb($postNmb) {
        $this->PostNmb = $postNmb;
    }

    function setPhone($phone) {
        $this->Phone = $phone;
    }

    function setCellphone($cellphone) {
        $this->Cellphone = $cellphone;
    }

    function setEmail($email) {
        $this->Email = $email;
    }

    function setBirthdate($birthdate) {
        $this->Birthdate = $birthdate;
    }

    function setNewsletter($newsletter) {
        $this->Newsletter = $newsletter;
    }

    function setHidden($hidden) {
        $this->Hidden = $hidden;
    }

    function setGender($gender) {
        $this->Gender = $gender;
    }
    function setSecretaddress($secretaddress) {
        $this->Secretaddress = $secretaddress;
    }
    function setComment($comment) {
        $this->Comment = $comment;
    }

    function name() {
        return $this->FirstName . " " . $this->LastName;
    }

    function id() {
        return $this->Id;
    }

    function getName($id) {
        $sql = "select firstname,lastname from " . $this->dbPrefix . "person where id = ?";
        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $id);
        $res = $prep->execute();
         
        if(count($res) == 0) {
            return "";
        }

        return $res[0]["firstname"]." ".$res[0]["lastname"];
    }

    function updatePortalPassword($personId, $password) {
        $pass = crypt($password, User::makesalt());

        $bind = $this->db->prepare("update ". $this->dbPrefix ."portal_user set pass=? where person=?");
        $bind->bind_params("si", $pass, $personId);

        $bind->execute();
    }

    function setPortalBlocked($personId, $blocked) {
        $bind = $this->db->prepare("update ". $this->dbPrefix ."portal_user set deactivated=? where person=?");
        $bind->bind_params("ii", $blocked, $personId);

        $bind->execute();
    }

    function removeUrlField($field, $id) {
        $prep = $this->db->prepare("update ". $this->dbPrefix ."portal_user set $field = '' where person = ?");
        $prep->bind_params("i", $id);
        $prep->execute();
    }



    function getSharedCompactPortalData() {
        function sortPerson($one, $two) {
            $res = strcasecmp($one["f"], $two["f"]);

            if($res) {
                return $res;
            }

            return strcasecmp($one["l"], $two["l"]);
        }

        $prep = $this->db->prepare("select person as p, ".
        "(if(show_firstname,firstname,'')) as f, (if(show_lastname,lastname,'')) as l, ".
        "(if(show_phone, phone,'')) as q, (if(show_cellphone,cellphone,'')) as c, ".
        "(if(show_gender, gender, '')) as g, (if(show_email, email, '')) as e, ".
        "(if(show_address, address, '')) as z, (if(show_city, city, '')) as x, ".
        "(if(show_postnmb, postnmb, '')) as v, (if(show_country, country, '')) as b, ".
        "(if(show_birthdate, birthdate, '')) as n, show_image as m, ".
        "(select min(year) from " . $this->dbPrefix . "year_membership where memberid=person) as y, ".
        "show_image as s, twitter as t, homepage as h, facebook as j, linkedin as k". 
        " from " . $this->dbPrefix . "portal_user," . $this->dbPrefix . "person where person = id and show_firstname");        

        $arr = $prep->execute();

        $accDate = new ezDate();
        $year = $accDate->year();

        $prepOther = $this->db->prepare("select id, firstname, lastname,(select min(year) from " . $this->dbPrefix . "year_membership where memberid=id) as yf ".
        " from " . $this->dbPrefix . "person, ". $this->dbPrefix . "year_membership ".
        " where not exists(select null from " . $this->dbPrefix . "portal_user where person=id) and id=memberid and year IN(?, ?) group by id");

        $prepOther->bind_params("ii", $year, $year-1);

        $arrOther = $prepOther->execute();

        foreach($arrOther as $one) {
            $arr[] = array("p" => $one["id"], "f" => $one["firstname"], "l" => $one["lastname"], "y" => $one["yf"]);
        }

        usort($arr, "sortPerson");

        return $arr;
    }

    function getAllPortal() {
        $sql = "select firstname, lastname, U.* from ". $this->dbPrefix . "person," . $this->dbPrefix . "portal_user U where id=person order by firstname, lastname";
        $prep = $this->db->prepare($sql);
        $res = $prep->execute();
        return $res;
    }

    function searchByEmailInDb($email, $dbprefix) {
        $email = "%".$email."%";
        $sql = "select id, secret from ".$dbprefix."person where email like ?";

        $prep = $this->db->prepare($sql);
        $prep->bind_params("s", $email);
        return $prep->execute();

    }

    function getOnePortal($id) {
        $sql = "select deactivated, firstname,lastname,email,address,postnmb,city,country,phone,cellphone,birthdate, gender,".
        		"show_gender, show_birthdate, show_cellphone, show_phone, show_country, show_city, show_postnmb, show_address, show_email, show_lastname, show_firstname, show_image, ".
                "homepage, twitter, facebook, linkedin, ifnull(newsletter, 0) as newsletter ".
        		"from " . $this->dbPrefix . "person," . $this->dbPrefix . "portal_user where id = ? and id=person";
        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $id);
        $res = $prep->execute();

        return array_pop($res);
    }

    function getOne($id) {
        $sql = "select * from " . $this->dbPrefix . "person where id = ?";
        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $id);
        $res = $prep->execute();

        return array_pop($res);
    }

    function load($id) {
        $fields = $this->getOne($id);

        if (!$fields) {
            return;
        }
        $this->Id = $id;
        $this->setIsEmployee($fields["employee"]);
        $this->setFirstname($fields["firstname"]);
        $this->setLastname($fields["lastname"]);
        $this->setEmail($fields["email"]);
        $this->setPostnmb($fields["postnmb"]);
        $this->setCity($fields["city"]);
        $this->setCountry($fields["country"]);
        $this->setPhone($fields["phone"]);
        $this->setCellphone($fields["cellphone"]);
        $this->setAddress($fields["address"]);
        $this->setNewsletter($fields["newsletter"]);
        $this->Secretaddress = $fields["secretaddress"];
        $this->Comment = $fields["comment"];

        if($fields["birthdate"]) {
            $tmpdate = new eZDate();
            $tmpdate->setMySQLDate($fields["birthdate"]);
            $this->setBirthdate($tmpdate->displayAccount());
        }
        $this->setHidden($fields["hidden"]);
        $this->setGender($fields["gender"]);
    }

    function getAll($isEmpoyee = 0) {
        $sql = "select id, firstname,lastname,email from " . $this->dbPrefix . "person" . ($isEmpoyee ? " where employee = 1" : "") . " order by lastname, firstname";
        $prep = $this->db->prepare($sql);
        $res = $prep->execute();

        return $res;
    }

    function savePortalUser($id, $data) {
        $bdSave = new eZDate();
        $bdSave->setDate($data->birthdate);
        $mysqlDate = $bdSave->mySQLDate();

        /* Take a backup */
        $prep = $this->db->prepare("insert ignore into " . $this->dbPrefix . "person_backup (id,firstname,lastname,email,address,postnmb,city,country,phone,cellphone,birthdate,newsletter,gender,lastedit) (select id,firstname,lastname,email,address,postnmb,city,country,phone,cellphone,birthdate,newsletter,gender,lastedit from " . $this->dbPrefix . "person where id = ?)");
        $prep->bind_params("i", $id);
        $prep->execute();

        $prep = $this->db->prepare("update " . $this->dbPrefix . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,birthdate=?,newsletter=?, gender=?, lastedit=now() where id = ?");
        $prep->bind_params("ssssssssssisi", $data->firstname, $data->lastname, $data->email, $data->address, $data->postnmb, $data->city, $data->country, $data->phone, $data->cellphone, $mysqlDate, $data->newsletter, $data->gender, $id);
        $prep->execute();

        $prep = $this->db->prepare("update " . $this->dbPrefix . "portal_user set show_gender=?, show_birthdate=?, show_cellphone=?, show_phone=?, show_country=?, ".
        							"show_city=?, show_postnmb=?, show_address=?, show_email=?, show_lastname=?, ".
        							"show_firstname=?, show_image=?,twitter=?,homepage=?,linkedin=?,facebook=? where person =? ");
        $prep->bind_params("iiiiiiiiiiiissssi", $data->show_gender, $data->show_birthdate, $data->show_cellphone, $data->show_phone, $data->show_country, $data->show_city, $data->show_postnmb, $data->show_address, $data->show_email, $data->show_lastname, $data->show_firstname, $data->show_image, $data->twitter, $data->homepage,$data->linkedin,$data->facebook,$id);
        $prep->execute();
    }

    function save() {

        $mysqlDate = NULL;

        if($this->Birthdate) {
            $bdSave = new eZDate();
            $bdSave->setDate($this->Birthdate);

            $mysqlDate = $bdSave->mySQLDate();
        }
        if ($this->Id) {
            $prep = $this->db->prepare("update " . $this->dbPrefix . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=?,birthdate=?,newsletter=?, hidden=?, gender=?, secretaddress=?,comment=?,lastedit=now() where id = ?");
            $prep->bind_params("sssssssssssiisisi", $this->FirstName, $this->LastName, $this->Email, $this->Address, $this->PostNmb, $this->City, $this->Country, $this->Phone, $this->Cellphone, $this->IsEmployee, $mysqlDate, $this->Newsletter, $this->Hidden, $this->Gender, $this->Secretaddress, $this->Comment, $this->Id);
            $prep->execute();
            return $this->db->affected_rows();
        }

        $prep = $this->db->prepare("insert into " . $this->dbPrefix . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=?,birthdate=?,newsletter=?,hidden=?,gender=?, secretaddress=?,comment=?,lastedit=now()");
        $prep->bind_params("sssssssssssiisis", $this->FirstName, $this->LastName, $this->Email, $this->Address, $this->PostNmb, $this->City, $this->Country, $this->Phone, $this->Cellphone, $this->IsEmployee, $mysqlDate, $this->Newsletter, $this->Hidden, $this->Gender,$this->Secretaddress, $this->Comment);
        $prep->execute();

        $this->id = $this->db->insert_id();
        return $this->id;
    }

    function search($incMemberInfo) {
        $cols = "*";
        if ($incMemberInfo) {
            $accStandard = new AccountStandard($this->db);
            $accSemester = new AccountSemester($this->db);
            $active_semester = addslashes($accStandard->getOneValue(AccountStandard::CONST_SEMESTER));
            $active_year = addslashes($accStandard->getOneValue(AccountStandard::CONST_YEAR));
            $cols = "*, (select distinct 1 from " . $this->dbPrefix . "train_membership where memberid=id and semester=$active_semester) as train" .
			", (select distinct 1 from " . $this->dbPrefix . "course_membership where memberid=id and semester=$active_semester) as course" .
			", (select distinct 1 from " . $this->dbPrefix . "youth_membership where memberid=id and semester=$active_semester) as youth" .
			", (select distinct 1 from " . $this->dbPrefix . "year_membership where memberid=id and year=$active_year) as year";
        }

        $searchWrap = $this->db->search("select $cols from " . $this->dbPrefix . "person", "order by lastname,firstname");

        $searchWrap->addAndParam("i", "id", $this->Id);
        $searchWrap->addAndParam("s", "firstname", $this->FirstName."%");
        $searchWrap->addAndParam("s", "lastname", $this->LastName."%");
        $searchWrap->addAndParam("i", "employee", $this->IsEmployee);
        $searchWrap->addAndParam("s", "address", $this->Address);
        $searchWrap->addAndParam("s", "postnmb", $this->PostNmb);
        $searchWrap->addAndParam("s", "city", $this->City);
        $searchWrap->addAndParam("s", "country", $this->Country);
        $searchWrap->addAndParam("s", "phone", $this->Phone);
        $searchWrap->addAndParam("s", "cellphone", $this->Cellphone);
        $searchWrap->addAndParam("s", "email", $this->Email);
        $searchWrap->addAndParam("i", "newsletter", $this->Newsletter);

        if($this->Gender == "U") {
            /* Appears that addOnlySql bugs if no other params are set */
            $searchWrap->addAndParam("i", "1", 1);
            $searchWrap->addOnlySql("gender is null");
        } else {
            $searchWrap->addAndParam("s", "gender", $this->Gender);
        }

        if($this->Hidden) {
            $searchWrap->addOnlySql("(hidden is null or hidden <> 1)");
        }
        $searchWrap->addAndQuery("s", $this->User, "exists (select null from " . $this->dbPrefix . "user where person=id and username=?)");

        $res = $searchWrap->execute();

        foreach($res as &$one) {
            if($one["secretaddress"]) {
                $one["address"] = "#SECRET#";
                $one["phone"] = "#SECRET#";
                $one["cellphone"] = "#SECRET#";
            }
        }

        return $res;
    }

    function allWithEmail() {
        $searchWrap = $this->db->search("select firstname, lastname, email,newsletter from " . $this->dbPrefix . "person where email is not null order by newsletter desc, lastname, firstname, email");
        return $searchWrap->execute();
    }

    function setSecret($id, $prefix = 0) {
        $secret = "";
        for ($i=0; $i<40; $i++) {
            $secret.= chr(mt_rand(97, 122));
        }

        if(!$prefix) {
            $prefix = $this->dbPrefix;
        }

        $prep = $this->db->prepare("update " . $prefix . "person set secret = ? where id = ?");
        $prep->bind_params("si", $secret, $id);
        $prep->execute();

        return $secret;
    }

    function getSecret($id) {
        $prep = $this->db->prepare("select secret from " . $this->dbPrefix . "person where id=?");
        $prep->bind_params("i", $id);
        $res = $prep->execute();

        if(!$res[0]["secret"]) {

            $secret = $this->setSecret($id);

            return $this->dbPrefix.":".$secret;
        }
        return $this->dbPrefix.":".$res[0]["secret"];
    }

    function requirePortaluserSecretMatchAndUpdateSecret($secret, $id, $prefix) {
        $prepins = $this->db->prepare("insert ignore into ".$prefix . "portal_user (person, show_firstname, show_lastname) values (?,1,1)");
        $prepins->bind_params("i", $id);
        $prepins->execute();

        $prep = $this->db->prepare("select id from " . $prefix . "person," . $prefix . "portal_user where secret=? and id =? and id=person");
        $prep->bind_params("si", $secret, $id);
        $res = $prep->execute();

        if(count($res) == 0) {
            return 0;
        }

        $this->setSecret($id, $prefix);

        return 1;
    }

    function unsubscribeToNewsletter($prefix, $secret, $id) {
        $prefix = Strings::whitelist($prefix);

        $prep = $this->db->prepare("update " . $prefix . "person set newsletter = 0 where secret = ? and id = ?");
        $prep->bind_params("si", $secret, $id);
        $prep->execute();
         
        return $this->db->affected_rows();
         
    }

    function getFirst() {
        $prep = $this->db->prepare("select * from ".$this->dbPrefix . "person limit 1");
        return $prep->execute();
    }

    function allChangedSince($date) {
        $prep = $this->db->prepare("select * from ".$this->dbPrefix . "person where firstname is not null and length(firstname) > 0 and lastedit >= ? and (hidden is null or hidden = 0)");
        $prep->bind_params("s", $date);

        $res =  $prep->execute();

        foreach($res as &$one) {
            if($one["secretaddress"]) {
                $one["address"] = "";
                $one["phone"] = "";
                $one["city"] = "";
                $one["postnmb"] = "";
                $one["cellphone"] = "";
            } else {
                $one["secretaddress"] = 0;
            }
            unset($one["hidden"]);
            unset($one["secret"]);

            if(!$one["employee"] || strlen($one["employee"] == 0)) {
                $one["employee"] = 0;
            }
            if(!$one["newsletter"] || strlen($one["newsletter"] == 0)) {
                $one["newsletter"] = 0;
            }


            foreach($one as $key => $value) {
                if($value === NULL) {
                    $one[$key] = "";
                }
            }
        }

        return $res;
    }
    
    function updateSecretIfUserMatches($email, $secret) {
        $prep = $this->db->prepare("select email, username from ".$this->dbPrefix .
        			"person P, ".$this->dbPrefix ."user U where P.email like ? and U.person = P.id");
        $prep->bind_params("s", '%'.$email.'%');

        $res = $prep->execute();

        if(count($res) != 1) {
            return array("error" => "Bad match:".count($res),"email"=>$email, "dbprefix" => $this->dbPrefix);
        }

        $registeredEmail = $res[0]["email"];

        $emails = explode(",", $registeredEmail);

        $found = false;
        foreach($emails as $one) {
            if($one == $email) {
                $found = true;
            }
        }

        if(!$found) {
            return array("error" => "email not unique", "email"=>$email);
        }

        $prep = $this->db->prepare("update ".$this->dbPrefix .
        			"person P set secret=? where exists (select null from ".$this->dbPrefix .
        						   "user U where P.email like ? and U.person = P.id)");
        $prep->bind_params("ss", $secret, '%'.$email.'%');

        $prep->execute();

        return array("email" => $registeredEmail, "username" => $res[0]["username"]);
    }
}