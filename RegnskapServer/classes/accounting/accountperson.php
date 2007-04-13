<?php
class AccountPerson {
	public $Id;
	public $FirstName;
	public $LasttName;
	public $IsEmpoyee;
	public $Address;
	public $PostNmb;
	public $Phone;
	public $Email;
	private $db;

	function AccountPerson($db, $id = 0, $firstname = 0, $lastname = 0, $isEmployee = 0, $address = 0, $postNmb = 0, $phone = 0, $email = 0) {
		$this->db = $db;
		$this->Id = $id;
		$this->FirstName = $firstname;
		$this->LastName = $lastname;
		$this->IsEmpoyee = $isEmployee;
		$this->Address = $address;
		$this->PostNmb = $postNmb;
		$this->Phone = $phone;
		$this->Email = $email;
	}

	function name() {
		return $this->FirstName . " " . $this->LastName;
	}

	function id() {
		return $this->Id;
	}

	function getAll($isEmpoyee = 0) {
		$sql = "select * from " . AppConfig :: DB_PREFIX . "person" . ($isEmpoyee ? " where employee = 1" : "")." order by lastname, firstname";
		$prep = $this->db->prepare($sql);
		$res = $prep->execute();

		return $res;
	}
}