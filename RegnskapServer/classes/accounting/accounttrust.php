<?php
class AccountTrust {
	private $db;
	public $Id;
	public $Fond;
	public $Description;
	public $Occured;
	public $Fond_account;
	public $Club_account;

	function AccountTrust($db, $fond = 0, $description = 0, $fond_account = 0, $club_account = 0, $occured = 0, $id = 0) {
		$this->db = $db;

		$this->Id = $id;
		$this->Fond = $fond;
		$this->Description = $description;
		$this->Occured = $occured;
		$this->Fond_account = $fond_account;
		$this->Club_account = $club_account;
	}

	function setDate($day, $month, $year) {
		$this->Occured = & new eZDate();
		$this->Occured->setDay($day);
		$this->Occured->setMonth($month);
		$this->Occured->setYear($year);

	}

	function store() {

		$date = $this->Occured->mySQLDate();

		$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "fond SET fond=?,description=?,occured=?,fond_account=?,club_account=?");
		$prep->bind_params("sssii", $date, $this->Fond, $this->Description, $this->Fond_account, $this->Club_account);
		$prep->execute();
		return $this->db->insert_id();
	}

	function getFondtypes() {

		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "fond_type");
		$arr = $prep->execute();

		$result = array ();

		foreach ($arr as $one) {
			$result[] = array (
				"fond" => $one["fond"],
				"description" => $one["description"]
			);
		}

		return $result;
	}

	function getFondSum($fond, $club = 0) {

		$field = $club ? "club_account" : "fond_account";

		$prep = $this->db->prepare("select sum($field) as s from " . AppConfig :: DB_PREFIX . "fond where fond = ?");
		$prep->bind_params("s", $fond);
		$arr = $prep->execute();

		foreach ($arr as $one) {
			return $one["s"];
		}
	}

	function getFondInfo($fond) {
		$prep = $this->db->prepare("select id, description, occured, fond_account, club_account from " . AppConfig :: DB_PREFIX . "fond where fond=? order by occured,id");
		$prep->bind_params("s", $fond);
		$arr = $prep->execute();

		$result = array ();

		foreach ($arr as $one) {
			$result[] = new AccountTrust($this->db, $fond, $one["description"], $one["fond_account"], $one["club_account"], $one["occured"], $one["id"]);
		}

		return $result;
	}

	function getId() {
		return $this->Id;
	}
	function getFond() {
		return $this->Fond;
	}
	function getDescription() {
		return $this->Description;
	}
	function getOccured() {
		return $this->Occured;
	}
	function getFond_account() {
		return $this->Fond_account;
	}
	function getClub_account() {
		return $this->Club_account;
	}
}
?>


