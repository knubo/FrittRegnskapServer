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
			$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "semester values ($p, '$s $i');");
			$prep->execute();
		}
	}

	function getSemesterName($id) {
		$prep = $this->db->prepare("select semester, description from " . AppConfig :: DB_PREFIX . "semester where semester = ?");

		$prep->bind_params("i", $id);

		$line_query = $prep->execute();

		if (count($line_query) >= 0) {
			return $line_query[0]["description"];
		}

		return "";
	}

    function getAll() {
    	$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "semester");
        return $prep->execute();
    }
}
?>
