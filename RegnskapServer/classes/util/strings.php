<?php
class Strings {

	function whitelist($dirty_data) {

		$dirty_array = str_split($dirty_data);
		$clean_data = "";
		foreach ($dirty_array as $char) {
			$clean_char = preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $char);
			$clean_data = $clean_data . $clean_char;
		}

		return $clean_data;
	}

}
?>
