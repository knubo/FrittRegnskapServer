<?php

/*
 * Created on Apr 5, 2007
 *
 */

class User {
	const AUTH_OK = 1;
	const AUTH_FAILED = 0;

	private $db;

	function __construct($dbi) {
		$this->db = $dbi;
	}

	function authenticate($username, $password) {

		$toBind = $this->db->prepare("select pass from " . (AppConfig :: DB_PREFIX) . "user where username = ?");
		
		$toBind->bind_param("s", $username);
		
		$result = $this->db->execute($toBind);

		if (!$result && !sizeof($result)) {
			return User::AUTH_FAILED;
		}
		
		$crypted = $result[0]["pass"];
		
		if (crypt($password, $crypted) == $crypted) {
			return User::AUTH_OK;
		}
		return User::AUTH_FAILED;
	}
}
?>
