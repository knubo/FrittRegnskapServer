<?php


/*
 * Created on Apr 5, 2007
 *
 */
class DB {

	private $link;

	function link() {
		return $this->link;
	}

	function __construct() {
		$this->link = mysqli_connect("127.0.0.1", "root", "", "knubo");
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit;
		}
	}

    function insert_id() {
    	return mysqli_insert_id($this->link);
    }

	function table_exists($table) {
		$result = $this->link->query("show tables like '" . $table . "'");

		$match = $result->num_rows;

		$result->close();

		return $match > 0;
	}

	function prepare($query) {
		$mysqli = mysqli_prepare($this->link, $query);

		if (!$mysqli) {
			die('Invalid query: ' . $this->link->error);
		}

		return new PrepWrapper($mysqli);
	}

	function action($query) {
		mysqli_query($this->link, $query);
	}

	function backtrace() {
		$output = "<div style='text-align: left; font-family: monospace;'>\n";
		$output .= "<b>Backtrace:</b><br />\n";
		$backtrace = debug_backtrace();

		foreach ($backtrace as $bt) {
			$args = '';
			foreach ($bt['args'] as $a) {
				if (!empty ($args)) {
					$args .= ', ';
				}
				switch (gettype($a)) {
					case 'integer' :
					case 'double' :
						$args .= $a;
						break;
					case 'string' :
						$a = htmlspecialchars(substr($a, 0, 64)) . ((strlen($a) > 64) ? '...' : '');
						$args .= "\"$a\"";
						break;
					case 'array' :
						$args .= 'Array(' . count($a) . ')';
						break;
					case 'object' :
						$args .= 'Object(' . get_class($a) . ')';
						break;
					case 'resource' :
						$args .= 'Resource(' . strstr($a, '#') . ')';
						break;
					case 'boolean' :
						$args .= $a ? 'True' : 'False';
						break;
					case 'NULL' :
						$args .= 'Null';
						break;
					default :
						$args .= 'Unknown';
				}
			}
			$output .= "<br />\n";
			$output .= "<b>file:</b> {$bt['line']} - {$bt['file']}<br />\n";
			$output .= "<b>call:</b> {$bt['class']}{$bt['type']}{$bt['function']}($args)<br />\n";
		}
		$output .= "</div>\n";
		return $output;
	}
	function affected_rows() {
		return $this->link->affected_rows;
	}
}

class PrepWrapper {
	private $Mysqli;

	function __construct($mysqli) {
		$this->Mysqli = $mysqli;
	}

	function execute() {
		$handle = $this->Mysqli;

		if (!$handle->execute()) {
			die("Klarte ikke kj¿re query.");
		}

		$metadata = $handle->result_metadata();

		# No rows, no result, no action.
		if ($metadata == FALSE) {
			return;
		}

		$nof = $metadata->field_count;

		# The metadata of all fields
		$fieldMeta = $metadata->fetch_fields();

		# convert it to a normal array just containing the field names
		$fields = array ();
		for ($i = 0; $i < $nof; $i++)
			$fields[$i] = $fieldMeta[$i]->name;

		# The idea is to get an array with the result values just as in mysql_fetch_assoc();
		# But we have to use call_user_func_array to pass the right number of args ($nof+1)
		# So we create an array:
		# array( $stmt, &$result[0], &$result[1], ... )
		# So we get the right values in $result in the end!

		# Prepare $result and $arg (which will be passed to bind_result)
		$result = array ();
		$arg = array (
			$handle
		);
		for ($i = 0; $i < $nof; $i++) {
			$result[$i] = '';
			$arg[$i +1] = & $result[$i];
		}

		call_user_func_array('mysqli_stmt_bind_result', $arg);

		$myall = array ();

		# after mysqli_stmt_fetch(), our result array is filled just perfectly,
		# but it is numbered (like in mysql_fetch_array() ), not indexed by field name!
		# Make it ordered by field name.
		while ($handle->fetch()) {
			$row = array ();

			for ($i = 0; $i < $nof; $i++) {
				$row[$fields[$i]] = $result[$i];
			}
			$myall[] = $row;
		}
		return $myall;
	}

	function bind_array_params($types, $args) {

		$allArgs = array_merge(array (
			$this->Mysqli,
			$types
		), $args);

		call_user_func_array('mysqli_stmt_bind_param', $allArgs);
	}

	function bind_params() {
		$args = func_get_args();

		call_user_func_array('mysqli_stmt_bind_param', 
		   array_merge(array ($this->Mysqli), $args));
	}
	
	

}
?>
