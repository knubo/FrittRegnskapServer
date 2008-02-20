<?php
class AccountBudget {

	private $db;

    public $PostType;
    public $Amount;

	function AccountBudget($db, $postType = 0, $amount = 0) {
		$this->db = $db;
        $this->PostType = $postType;
        $this->Amount = $amount;
	}

    function getBudgetlines($year) {

    }

	function getMemberships($year) {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "budget_membership where year = ?");
		$prep->bind_params("i", $year);

		$res = $prep->execute();

		if (count($res) == 0) {
			return array (
				"year" => $year,
				"year_members" => 0,
				"spring_train" => 0,
				"spring_course" => 0,
				"fall_train" => 0,
				"fall_course" => 0
			);
		}

		return array_shift($res);
	}

	function saveMemberships($keyYear, $keyFall, $year, $course, $train) {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "budget_membership where year = ?");
		$prep->bind_params("i", $keyYear);

		$res = $prep->execute();

		if (count($res) == 0) {
			$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "budget_membership set year=?");
			$prep->bind_params("i", $keyYear);
			$res = $prep->execute();
		}

		if ($keyFall == 0) {
			$prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "budget_membership set spring_train=?,spring_course=?,year_members=? where year=?");
			$prep->bind_params("iiii", $train, $course, $year, $keyYear);
			$prep->execute();

			return $this->db->affected_rows();
		}

		$prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "budget_membership set fall_train=?,fall_course=? where year=?");
		$prep->bind_params("iii", $train, $course, $keyYear);
		$prep->execute();

		return $this->db->affected_rows();

	}
}
?>
