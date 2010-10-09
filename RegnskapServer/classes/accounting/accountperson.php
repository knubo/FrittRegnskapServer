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

    function AccountPerson($db) {
        $this->db = $db;
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
        $sql = "select firstname,lastname from " . AppConfig::pre() . "person where id = ?";
        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $id);
        $res = $prep->execute();
         
        if(count($res) == 0) {
            return "";
        }

        return $res[0]["firstname"]." ".$res[0]["lastname"];
    }

    function getOnePortal($id) {
        $sql = "select firstname,lastname,email,address,postnmb,city,country,phone,cellphone,birthdate,newsletter, gender,".
        		"show_gender, show_birthdate, show_cellphone, show_phone, show_country, show_city, show_postnmb, show_address, show_email, show_lastname, show_firstname, show_image ".
        		"from " . AppConfig::pre() . "person," . AppConfig::pre() . "portal_user where id = ? and id=person";
        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $id);
        $res = $prep->execute();

        return array_pop($res);
    }

    function getOne($id) {
        $sql = "select * from " . AppConfig::pre() . "person where id = ?";
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
        $sql = "select id, firstname,lastname,email from " . AppConfig::pre() . "person" . ($isEmpoyee ? " where employee = 1" : "") . " order by lastname, firstname";
        $prep = $this->db->prepare($sql);
        $res = $prep->execute();

        return $res;
    }

    function savePortalUser($id, $data) {
        $bdSave = new eZDate();
        $bdSave->setDate($data->birthdate);
        $mysqlDate = $bdSave->mySQLDate();


        $prep = $this->db->prepare("update " . AppConfig::pre() . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,birthdate=?,newsletter=?, gender=?, lastedit=now() where id = ?");
        $prep->bind_params("ssssssssssisi", $data->firstname, $data->lastname, $data->email, $data->address, $data->postnmb, $data->city, $data->country, $data->phone, $data->cellphone, $mysqlDate, $data->newsletter, $data->gender, $id);
        $prep->execute();
        
        $prep = $this->db->prepare("update " . AppConfig::pre() . "portal_user set show_gender=?, show_birthdate=?, show_cellphone=?, show_phone=?, show_country=?, show_city=?, show_postnmb=?, show_address=?, show_email=?, show_lastname=?, show_firstname=?, show_image=? where person =? ");
        $prep->bind_params("iiiiiiiiiiiii", $data->show_gender, $data->show_birthdate, $data->show_cellphone, $data->show_phone, $data->show_country, $data->show_city, $data->show_postnmb, $data->show_address, $data->show_email, $data->show_lastname, $data->show_firstname, $data->show_image, $id);
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
            $prep = $this->db->prepare("update " . AppConfig::pre() . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=?,birthdate=?,newsletter=?, hidden=?, gender=?, secretaddress=?,comment=?,lastedit=now() where id = ?");
            $prep->bind_params("sssssssssssiisisi", $this->FirstName, $this->LastName, $this->Email, $this->Address, $this->PostNmb, $this->City, $this->Country, $this->Phone, $this->Cellphone, $this->IsEmployee, $mysqlDate, $this->Newsletter, $this->Hidden, $this->Gender, $this->Secretaddress, $this->Comment, $this->Id);
            $prep->execute();
            return $this->db->affected_rows();
        }

        $prep = $this->db->prepare("insert into " . AppConfig::pre() . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=?,birthdate=?,newsletter=?,hidden=?,gender=?, secretaddress=?,comment=?,lastedit=now()");
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
            $cols = "*, (select distinct 1 from " . AppConfig::pre() . "train_membership where memberid=id and semester=$active_semester) as train" .
			", (select distinct 1 from " . AppConfig::pre() . "course_membership where memberid=id and semester=$active_semester) as course" .
			", (select distinct 1 from " . AppConfig::pre() . "youth_membership where memberid=id and semester=$active_semester) as youth" .
			", (select distinct 1 from " . AppConfig::pre() . "year_membership where memberid=id and year=$active_year) as year";
        }

        $searchWrap = $this->db->search("select $cols from " . AppConfig::pre() . "person", "order by lastname,firstname");

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
        $searchWrap->addAndQuery("s", $this->User, "exists (select null from " . AppConfig::pre() . "user where person=id and username=?)");

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
        $searchWrap = $this->db->search("select firstname, lastname, email,newsletter from " . AppConfig::pre() . "person where email is not null order by newsletter desc, lastname, firstname, email");
        return $searchWrap->execute();
    }

    function getSecret($id) {
        $prep = $this->db->prepare("select secret from " . AppConfig::pre() . "person where id=?");
        $prep->bind_params("i", $id);
        $res = $prep->execute();

        if(!$res[0]["secret"]) {
            $secret = "";
            for ($i=0; $i<40; $i++) {
                $secret.= chr(mt_rand(97, 122));
            }
            $prep = $this->db->prepare("update " . AppConfig::pre() . "person set secret = ? where id = ?");
            $prep->bind_params("si", $secret, $id);
            $prep->execute();

            return AppConfig::pre().":".$secret;
        }
        return AppConfig::pre().":".$res[0]["secret"];
    }

    function unsubscribeToNewsletter($prefix, $secret, $id) {
        $prefix = Strings::whitelist($prefix);

        $prep = $this->db->prepare("update " . $prefix . "person set newsletter = 0 where secret = ? and id = ?");
        $prep->bind_params("si", $secret, $id);
        $prep->execute();
         
        return $this->db->affected_rows();
         
    }

    function getFirst() {
        $prep = $this->db->prepare("select * from ".AppConfig::pre() . "person limit 1");
        return $prep->execute();
    }

    function allChangedSince($date) {
        $prep = $this->db->prepare("select * from ".AppConfig::pre() . "person where firstname is not null and length(firstname) > 0 and lastedit >= ? and (hidden is null or hidden = 0)");
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
}