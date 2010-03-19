<?php
class AccountSemesterMembership {
	public $Semester;
	public $User;
	public $Regn_line;
    public $Text;
	private $db;
	private $Type;

	function AccountSemesterMembership($db, $type = 0, $user = 0, $semester = 0, $regn_line = 0, $desc = 0) {
		$this->db = $db;
		$this->Type = $type;
		$this->Semester = $semester;
		$this->User = $user;
		$this->Regn_line = $regn_line;
        $this->Text = $desc;
	}

	function addCreditPost($line, $amount) {

	    $standard = new AccountStandard($this->db);
	    
		switch ($this->Type) {
			case "course" :
				$postType = $standard->getOneValue(AccountStandard::CONST_BUDGET_COURSE_POST);
				break;
			case "train" :
				$postType = $standard->getOneValue(AccountStandard::CONST_BUDGET_TRAIN_POST);
				break;
            case "youth" :
                $postType = $standard->getOneValue(AccountStandard::CONST_BUDGET_YOUTH_POST);
                break;
		}

		$post = new AccountPost($this->db, $line, "-1", $postType, $amount);
		return $post->store();

	}

	function addDebetPost($line, $postType, $amount) {
		$post = new AccountPost($this->db, $line, "1", $postType, $amount);
		return $post->store();
	}

	function getAllMemberNames($semester) {
		/* Using group by here due to previous bug which added duplicate entries. */
		$prep = $this->db->prepare("select firstname, lastname, id from " . AppConfig :: DB_PREFIX . "person P," . AppConfig :: DB_PREFIX . $this->Type . "_membership C where C.memberid = P.id and semester=? group by lastname, firstname,id order by lastname, firstname");
		$prep->bind_params("i", $semester);
		$query_array = $prep->execute();

		$result = array ();

		foreach ($query_array as $one) {
			$result[] = array (
				$one["firstname"],
				$one["lastname"],
				$one["id"]
			);
		}
		return $result;

	}


    function delete($semester, $person) {
        $prep = $this->db->prepare("delete from " . AppConfig :: DB_PREFIX . $this->Type."_membership where memberid = ? and semester=?");
        $prep->bind_params("ii", $person, $semester);
        $prep->execute();
        return $this->db->affected_rows();
    }

	function getUserMemberships($user, $type) {

		$prep = $this->db->prepare("select M.memberid, M.semester, M.regn_line, S.description from " . AppConfig :: DB_PREFIX . $type."_membership M, " . AppConfig :: DB_PREFIX ."semester S where memberid = ? and S.semester = M.semester group by memberid, semester, regn_line order by semester");
        $prep->bind_params("i", $user);
		$query_array = $prep->execute();

		$result = array ();

		foreach ($query_array as $one) {
			$result[] = & new AccountSemesterMembership(null, $type, $user, $one["semester"], $one["regn_line"], $one["description"]);
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

		$sql = "insert into " . AppConfig :: DB_PREFIX . $this->Type . "_membership set semester = ?, memberid=?, regn_line=?";
		$prep = $this->db->prepare($sql);

		$prep->bind_params("iii", $this->Semester, $this->User, $this->Regn_line);

		$prep->execute();

		return $this->db->affected_rows();
	}

    function getOverview() {
        $prep = $this->db->prepare("select count(*) as C, M.semester,fall,year from " . AppConfig :: DB_PREFIX . $this->Type . "_membership M," . AppConfig :: DB_PREFIX ."semester S where S.semester=M.semester group by semester;");
        return $prep->execute();
    }


	function course() {
		return "course";
	}

	function train() {
		return "train";
	}

    function youth() {
    	return "youth";
    }
}
?>
