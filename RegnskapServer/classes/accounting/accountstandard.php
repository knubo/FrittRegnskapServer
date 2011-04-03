<?php
class AccountStandard {

	const CONST_YEAR = "STD_YEAR";
	const CONST_MONTH = "STD_MONTH";
	const CONST_SEMESTER = "STD_SEMESTER";
	const CONST_EMAIL_SENDER = "STD_EMAIL_SENDER";
	const CONST_MASSLETTER_DUE_DATE = "MASSLETTER_DUE_DATE";
	const CONST_BUDGET_YEAR_POST = "BDG_YEAR_POST";
	const CONST_BUDGET_COURSE_POST = "BDG_COURSE_POST";
	const CONST_BUDGET_TRAIN_POST = "BDG_TRAIN_POST";
	const CONST_BUDGET_YOUTH_POST = "BDG_YOUTH_POST";
	const CONST_END_YEAR_POST = "END_YEAR_POST";
	const CONST_IB_POST = "FIRST_IB_POST";
	const CONST_END_MONTH_POST = "END_MONTH_POST";
	const CONST_FORDRINGER_POSTS = "FORDRINGER_POSTS";
	const CONST_END_MONTH_TRANSFER_POSTS = "END_MONTH_TRPOSTS";
	const CONST_REGISTER_MEMBERSHIP_POSTS = "REGI_MEMB_POSTS";
	const CONST_BIRTHDATE_REQUIRED = "BIRTHDATE_REQ";
	const CONST_FIRST_TIME_SETUP = "FIRST_TIME";
    const CONST_NO_DELETE_USERS = "DISABLE_DEL_USER";
    const CONST_INTEGRATION_SECRET = "INTEGRATION_SEC";
    const CONST_INTEGRATION_EMAIL = "INTEGRATION_EMAIL";
    
	private $db;

	function AccountStandard($db, $prefix = 0) {
		$this->db = $db;
		
		if($prefix) {
		    $this->prefix = $prefix;
		} else {
		    $this->prefix = AppConfig :: pre();
		} 
		
	}


	function setValue($id, $value) {

		$prep = $this->db->prepare("select * from " . $this->prefix . "standard where id=?");
		$prep->bind_params("s", $id);
		$query_array = $prep->execute();

		if (count($query_array) > 0) {
			$prep = $this->db->prepare("update " . $this->prefix . "standard set value=? where id=?");
			$prep->bind_params("ss", $value, $id);
			$prep->execute();

			return $this->db->affected_rows();
		} else {
			$prep = $this->db->prepare("insert into " . $this->prefix . "standard (id, value) values (?,?)");
			$prep->bind_params("ss", $id, $value);
			$prep->execute();

			return $this->db->affected_rows();
		}
	}

	function getValue($id) {
		$prep = $this->db->prepare("select value from " . $this->prefix . "standard where id=?");
		$prep->bind_params("s", $id);

		$return_array = array ();

		$query_array = $prep->execute();
		if (count($query_array) >= 0) {
			for ($i = 0; $i < count($query_array); $i++) {
				$return_array[$i] = $query_array[$i]["value"];
			}
		}

    	return $return_array;
	}

	static function question($q) {
		return "?";
	}
	static function i($q) {
		return "i";
	}

	function getValues($ids) {
		$params = array_map(array("AccountStandard","question"), $ids);
		$vals = array_map(array("AccountStandard","i"), $ids);

		$prep = $this->db->prepare("select value,id from " . $this->prefix . "standard where id IN(" . implode(",", $params) . ")");
		$prep->bind_array_params(implode("", $vals), $ids);

		$return_array = array ();
		$query_array = $prep->execute();
		if (count($query_array) >= 0) {
			foreach ($query_array as $one) {
				$return_array[$one["id"]] = $one["value"];
			}
		}

		return $return_array;

	}

	function getOneValue($id) {
		$res = $this->getValue($id);

		if (count($res)) {
			return $res[0];
		}
	}

	function getOneValueAsArray($id) {
		$res = $this->getOneValue($id);

		return explode(",", $res);
	}
}
?>
