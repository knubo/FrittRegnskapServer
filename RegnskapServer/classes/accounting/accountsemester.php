<?php
class AccountSemester {
	private $db;

	function AccountSemester($db) {
		$this->db = $db;
	}

	function dummyinit() {
		$i = 1990;
		for ($p = 1; $p < 50; $p++) {
			$s = ($p % 2) ? "H&oslash;st" : "V&aring;r";
			if (!($p % 2)) {
				$i++;
			}
			$prep = $this->db->prepare("insert into " . AppConfig::pre() . "semester values ($p, '$s $i');");
			$prep->execute();
		}
	}

	function getSemesterName($id) {
		$prep = $this->db->prepare("select semester, description from " . AppConfig::pre() . "semester where semester = ?");

		$prep->bind_params("i", $id);

		$line_query = $prep->execute();

		if (count($line_query) >= 0) {
			return $line_query[0]["description"];
		}

		return "";
	}

	function getAll() {
		$prep = $this->db->prepare("select * from " . AppConfig::pre() . "semester");
		return $prep->execute();
	}

    function getForYear($year) {
    	$prep = $this->db->prepare("select * from " . AppConfig::pre() . "semester where year=? order by fall");
        $prep->bind_params("i", $year);
        return $prep->execute();
    }

	function hasEntry($year, $fall) {
		$prep = $this->db->prepare("select description from " . AppConfig::pre() . "semester where year=? and fall=?");
		$prep->bind_params("ii", $year, $fall);
		$res = $prep->execute();

		return count($res) == 0 ? false : true;
	}

	function save($year, $fall, $spring) {
		$updatePrep = $this->db->prepare("update " . AppConfig::pre() . "semester set description = ? where year = ? and fall = ?");
		$insertPrep = $this->db->prepare("insert into " . AppConfig::pre() . "semester set description = ?, year = ?, fall = ?");

		$res = 0;

		if ($this->saveEntry($updatePrep, $insertPrep, $year, $spring, 0)) {
			$res = 1;
		}
		if ($this->saveEntry($updatePrep, $insertPrep, $year, $fall, 1)) {
			$res = 1;
		}

		return $res;
	}

	function saveEntry($updatePrep, $insertPrep, $year, $spring, $fall) {
		if ($this->hasEntry($year, $fall)) {
			$updatePrep->bind_params("sii", $spring, $year, $fall);
			$updatePrep->execute();

			return $this->db->affected_rows() != 0;
		} else {
			$insertPrep->bind_params("sii", $spring, $year, $fall);
			$insertPrep->execute();
			return true;
		}
	}
	
}
?>
