<?php
class AccountPerson {
	public $Id;
	public $FirstName;
	public $LasttName;
	public $IsEmpoyee;
	public $Address;
	public $PostNmb;
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

	function getAll($isEmpoyee = 0) {
		$sql = "select * from " . AppConfig :: DB_PREFIX . "person" . ($isEmpoyee ? " where employee = 1" : "") . " order by lastname, firstname";
		$prep = $this->db->prepare($sql);
		$res = $prep->execute();

		return $res;
	}
	
	function save() {
		
	}
}