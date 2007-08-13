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

	function name() {
		return $this->FirstName . " " . $this->LastName;
	}

	function id() {
		return $this->Id;
	}

	function getOne($id) {
		$sql = "select * from " . AppConfig :: DB_PREFIX . "person where id = ?";
		$prep = $this->db->prepare($sql);
		$prep->bind_params("i", $id);
		$res = $prep->execute();

        return array_pop($res);
	}
	
	function load($id) {
		$fields = $this->getOne($id);
		
		if(!$fields) {
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
        $tmpdate = new eZDate();
        $tmpdate->setMySQLDate($fields["birthdate"]);
        $this->setBirthdate($tmpdate->displayAccount());
	}	


	function getAll($isEmpoyee = 0) {
		$sql = "select * from " . AppConfig :: DB_PREFIX . "person" . ($isEmpoyee ? " where employee = 1" : "") . " order by lastname, firstname";
		$prep = $this->db->prepare($sql);
		$res = $prep->execute();

		return $res;
	}

	function save() {
        
        $bdSave = new eZDate();
        $bdSave->setDate($this->Birthdate);
        
        $mysqlDate = $bdSave->mySQLDate();

		if ($this->Id) {
			$prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=?,birthdate=?,newsletter=? where id = ?");
			$prep->bind_params("sssssssssssii", $this->FirstName, $this->LastName, $this->Email, $this->Address, $this->PostNmb, $this->City, $this->Country, $this->Phone, $this->Cellphone, $this->IsEmployee, $mysqlDate, $this->Newsletter, $this->Id);
			$prep->execute();
			return $this->db->affected_rows();
		}

		$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=?,birthdate=?,newsletter=? ");
		$prep->bind_params("sssssssssssi", $this->FirstName, $this->LastName, $this->Email, $this->Address, $this->PostNmb, $this->City, $this->Country, $this->Phone, $this->Cellphone, $this->IsEmployee, $mysqlDate, $this->Newsletter);
		$prep->execute();

		$this->id = $this->db->insert_id();
		return $this->id;
	}

	function search($incMemberInfo) {
		$cols = "*";
		if ($incMemberInfo) {
			$accStandard = new AccountStandard($this->db);
			$accSemester = new AccountSemester($this->db);
			$active_semester = addslashes($accStandard->getOneValue("STD_SEMESTER"));
			$active_year = addslashes($accStandard->getOneValue("STD_YEAR"));
			$cols = "*, (select distinct 1 from regn_train_membership where memberid=id and semester=$active_semester) as train".
			", (select distinct 1 from regn_course_membership where memberid=id and semester=$active_semester) as course".
			", (select distinct 1 from regn_year_membership where memberid=id and year=$active_year) as year";
		}

		$searchWrap = $this->db->search("select $cols from " . AppConfig :: DB_PREFIX . "person", "order by lastname,firstname");

		$searchWrap->addAndParam("s", "firstname", $this->FirstName);
		$searchWrap->addAndParam("s", "lastname", $this->LastName);
		$searchWrap->addAndParam("i", "employee", $this->IsEmployee);
		$searchWrap->addAndParam("s", "address", $this->Address);
		$searchWrap->addAndParam("s", "postnmb", $this->PostNmb);
		$searchWrap->addAndParam("s", "city", $this->City);
		$searchWrap->addAndParam("s", "country", $this->Country);
		$searchWrap->addAndParam("s", "phone", $this->Phone);
		$searchWrap->addAndParam("s", "cellphone", $this->Cellphone);
		$searchWrap->addAndParam("s", "email", $this->Email);
		$searchWrap->addAndParam("i", "newsletter", $this->Newsletter);
        $searchWrap->addAndQuery("s", $this->User, "exists (select null from regn_user where person=id and username=?)");
        
	   	return $searchWrap->execute();
	}

}