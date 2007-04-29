<?php
class AccountPerson {
	public $Id;
	public $FirstName;
	public $LastName;
	public $IsEmpoyee;
	public $Address;
	public $PostNmb;
	public $City;
	public $Phone;
	public $Cellphone;
	public $Email;
	private $db;

	function AccountPerson($db) {
		$this->db = $db;
	}
	function setId($id) {
		$this->Id = $id;
	}
	function setFirstname($firstname) {
		$this->FirstName = $firstname;
	}
	function setLastname($lastname) {
		$this->LastName = $lastname;
	}
	function setIsEmployee($isEmpoyee) {
		$this->IsEmpoyee = $isEmpoyee;
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

		foreach($res as $one) {
			return $one;
		}
		return 0;
	}

	function getAll($isEmpoyee = 0) {
		$sql = "select * from " . AppConfig :: DB_PREFIX . "person" . ($isEmpoyee ? " where employee = 1" : "") . " order by lastname, firstname";
		$prep = $this->db->prepare($sql);
		$res = $prep->execute();

		return $res;
	}

	function save() {

		if ($this->Id) {
			$prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=? where id = ?");
			$prep->bind_params("ssssssssssi", $this->FirstName, $this->LastName, $this->Email, $this->Address, $this->PostNmb, $this->City, $this->Country, $this->Phone, $this->Cellphone, $this->IsEmpoyee, $this->Id);
			$prep->execute();
			return $this->db->affected_rows();
		}

		$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "person set firstname=?,lastname=?,email=?,address=?,postnmb=?,city=?,country=?,phone=?,cellphone=?,employee=? ");
		$prep->bind_params("ssssssssss", $this->FirstName, $this->LastName, $this->Email, $this->Address, $this->PostNmb, $this->City, $this->Country, $this->Phone, $this->Cellphone, $this->IsEmpoyee);
		$prep->execute();

		$this->id = $this->db->insert_id();
		return $this->id;
	}

	function search() {
		$searchWrap = $this->db->search("select * from " . AppConfig :: DB_PREFIX . "person", "order by lastname,firstname");

		$searchWrap->addAndParam("s", "firstname", $this->FirstName);
		$searchWrap->addAndParam("s", "lastname", $this->LastName);
		$searchWrap->addAndParam("i", "employee", $this->IsEmpoyee);
		$searchWrap->addAndParam("s", "address", $this->Address);
		$searchWrap->addAndParam("s", "postnmb", $this->PostNmb);
		$searchWrap->addAndParam("s", "city", $this->City);
		$searchWrap->addAndParam("s", "country", $this->Country);
		$searchWrap->addAndParam("s", "phone", $this->Phone);
		$searchWrap->addAndParam("s", "cellphone", $this->Cellphone);
		$searchWrap->addAndParam("s", "email", $this->Email);
		
		return $searchWrap->execute();
	}
}