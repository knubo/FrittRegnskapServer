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
		$post = new AccountPost($this->db, $line, "-1", $postType, $amount);
		return $post->store();

	}

	function addDebetPost($line, $postType, $amount) {
		$post = new AccountPost($this->db, $line, "1", $postType, $amount);
		return $post->store();
	}

	function getAllMemberNames($year) {
		/* Using group by here due to previous bug which added duplicate entries. */
		$prep = $this->db->prepare("select firstname, lastname,id from " . AppConfig :: DB_PREFIX . "person P," . AppConfig :: DB_PREFIX . "year_membership C where C.memberid = P.id and C.year=? group by P.firstname,P.lastname order by P.lastname, P.firstname");
		$prep->bind_params("i", $year);
		$query_array = $prep->execute();

		$result = array ();

		foreach ($query_array as $one) {
			$result[] = array (
				$one["lastname"],
				$one["firstname"],
				$one["id"]
			);
		}
		return $result;

	}

//	function getUserMemberships($user) {
//
//		$prep = $this->db->prepare("select memberid, year, regn_line from " . AppConfig :: DB_PREFIX . "year_membership where memberid = ? group by memberid, year, regn_line order by year");
//
//		$query_array = $prep->execute();
//
//		$result = array ();
//
//		foreach ($query_array as $one) {
//			$result[] = & new eZAccountMembership($user, $this->Type, $one["year"], $one["regn_line"]);
//		}
//		return $result;
//	}

	function store() {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "year_membership where year = ? and memberid=?");
		$prep->bind_params("ii", $this->Year, $this->User);
		$res = $prep->execute();

		if (sizeof($res)) {
			return;
		}

		$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "year_membership set year = ?, memberid=?, regn_line=?");

		$prep->bind_params("iii", $this->Year, $this->User, $this->Regn_line);

		$prep->execute();

		return $this->db->affected_rows();
	}

	function getReportUsersBirthdate($year) {
		$prep = $this->db->prepare("select distinct id as id, firstname as firstname, lastname as lastname, birthdate as birthdate from " . AppConfig :: DB_PREFIX . "year_membership, " . AppConfig :: DB_PREFIX . "person where memberid=id and year=? order by birthdate desc,lastname,firstname");
		$prep->bind_params("i", $year);
		$res = $prep->execute();

		$arr = array ();
		foreach ($res as $one) {
            
            $d = "";
            if(array_key_exists("birthdate", $one) && $one["birthdate"]) {
    			$tmpdate = new eZDate();
	       		$tmpdate->setMySQLDate($one["birthdate"]);
            	$d = $tmpdate->displayAccount();
            }
            
			$arr[] = new ReportUserBirthdate($one["id"], $one["firstname"], $one["lastname"], $d);
		}

		return $arr;
	}
    
    function getReportUsersFull($year) {
        $prep = $this->db->prepare("select distinct * from " . AppConfig :: DB_PREFIX . "year_membership, " . AppConfig :: DB_PREFIX . "person where memberid=id and year=? order by lastname,firstname");
        $prep->bind_params("i", $year);
        $res = $prep->execute();

        return $res;
    }
}
?>
