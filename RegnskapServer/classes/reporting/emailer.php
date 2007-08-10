<?php

/*
 * Created on Aug 9, 2007
 *
 */

class Emailer {

	private $db;

	function Emailer($db) {
		$this->db = $db;
	}

	function sendEmail($subject, $email, $body, $sender) {
		$headers = "From :" . $sender . "\r\n" .
		"Reply-To: " . $sender . "\r\n" .
		'X-Mailer: PHP/' . phpversion();

		return mail($email, $subject, $body, $headers);
	}

}
?>
