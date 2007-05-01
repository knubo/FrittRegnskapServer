<?php
class AccountyearMembership {
	public $Year;
	public $User;
	public $Regn_line;
	private $db;

	function AccountyearMembership($db, $user = 0, $year = 0, $regn_line = 0) {
		$this->db = $db;
		$this->Year = $year;
		$this->User = $user;
		$this->Regn_line = $regn_line;
	}

	function addCreditPost($line, $amount) {

		$postType = AppConfig :: YearMembershipCreditPost;
		$post = new AccountPost($line, "-1", $postType, $amount);
		$post->store();

	}

	function addDebetPost($line, $postType, $amount) {
		$post = new AccountPost($line, "1", $postType, $amount);
		$post->store();
	}

	function getAllMemberNames($year) {
		$prep = $this->db->prepare("select firstname, lastname from " . AppConfig :: DB_PREFIX . "person P," . AppConfig :: DB_PREFIX . "year_membership C where C.memberid = P.id and year=? order by lastname, firstname");
		$prep->bind_params("i", $year);
		$query_array = $prep->execute();

		$result = array ();

		foreach ($query_array as $one) {
			$result[] = $one["FirstName"] . "," . $one["LastName"];
		}
		return $result;

	}

	function getUserMemberships($user) {

		$prep = $this->db->prepare("select memberid, year, regn_line from " . AppConfig :: DB_PREFIX . "year_membership where memberid = ? order by year");

		$query_array = $prep->execute();

		$result = array ();

		foreach ($query_array as $one) {
			$result[] = & new eZAccountMembership($user, $this->Type, $one["year"], $one["regn_line"]);
		}
		return $result;
	}

	function store() {
		$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "year_membership set year = ?, memberid=?, regn_line=?");

		$prep->bind_params("iii", $this->year, $this->User, $this->Regn_line);

		$prep->execute();

		return $this->db->affected_rows();
	}
}
?>
