<?php
class AccountSemesterMembership {
	public $Semester;
	public $User;
	public $Regn_line;
	private $db;
	private $Type;

	function AccountSemesterMembership($db, $type, $user = 0, $semester = 0, $regn_line = 0) {
		$this->db = $db;
		$this->Type = $type;
		$this->Semester = $semester;
		$this->User = $user;
		$this->Regn_line = $regn_line;
	}

	function addCreditPost($line, $amount) {

		switch ($this->Type) {
			case "course" :
				$postType = AppConfig :: CourseMembershipCreditPost;
				break;
			case "train" :
				$postType = AppConfig :: TrainMembershipCreditPost;
				break;
		}

		$post = new AccountPost($line, "-1", $postType, $amount);
		$post->store();

	}

	function addDebetPost($line, $postType, $amount) {
		$post = new AccountPost($line, "1", $postType, $amount);
		$post->store();
	}

	function getAllMemberNames($semester) {
		/* Using group by here due to previous bug which added duplicate entries. */
		$prep = $this->db->prepare("select firstname, lastname from " . AppConfig :: DB_PREFIX . "person P," . AppConfig :: DB_PREFIX . $this->Type . "_membership C where C.memberid = P.id and semester=? group by lastname, firstname order by lastname, firstname");
		$prep->bind_params("i", $semester);
		$query_array = $prep->execute();

		$result = array ();

		foreach ($query_array as $one) {
			$result[] = array (
				$one["firstname"],
				$one["lastname"]
			);
		}
		return $result;

	}

	function getUserMemberships($user) {

		$prep = $this->db->prepare("select memberid, semester, regn_line from " . AppConfig :: DB_PREFIX . $this->Type . "_membership where memberid = ? group by memberid, semester, regn_line order by semester");

		$query_array = $prep->execute();

		$result = array ();

		foreach ($query_array as $one) {
			$result[] = & new eZAccountMembership($user, $this->Type, $one["semester"], $one["regn_line"]);
		}
		return $result;
	}

	function store() {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . $this->Type . "_membership where semester = ? and memberid=?");
		$prep->bind_params("ii", $this->Semester, $this->User);
		$res = $prep->execute();

		if (sizeof($res)) {
			return;
		}

		$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . $this->Type . "_membership set semester = ?, memberid=?, regn_line=?");

		$prep->bind_params("iii", $this->Semester, $this->User, $this->Regn_line);

		$prep->execute();

		return $this->db->affected_rows();
	}

}
?>
