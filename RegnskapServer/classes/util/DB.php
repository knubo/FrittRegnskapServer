<?php

/*
 * Created on Apr 5, 2007
 *
 */
class DB {

	private $link;

    function __construct() {
       $this->link = mysqli_connect("127.0.0.1","root","","knubo");
       if (mysqli_connect_errno()) {
		  printf("Connect failed: %s\n", mysqli_connect_error());
      	  exit;
       }
    }

	function prepare($query) {
		$mysqli = mysqli_prepare($this->link, $query);
	
		if (!$mysqli) {
			die('Invalid query: ' . $mysqli->error);
		}
		
		return $mysqli;
	}
	
	function execute($handle) {
		
		if(!$handle->execute()) {
			die("Klarte ikke kj¿re query.");
		}
		
   	    $nof = mysqli_num_fields( mysqli_stmt_result_metadata($handle) );

        # The metadata of all fields
        $fieldMeta = mysqli_fetch_fields( mysqli_stmt_result_metadata($handle) );
       
		# convert it to a normal array just containing the field names
		$fields = array();
		for($i=0; $i < $nof; $i++)
		    $fields[$i] = $fieldMeta[$i]->name;
		
		# The idea is to get an array with the result values just as in mysql_fetch_assoc();
		# But we have to use call_user_func_array to pass the right number of args ($nof+1)
		# So we create an array:
		# array( $stmt, &$result[0], &$result[1], ... )
		# So we get the right values in $result in the end!
		
		# Prepare $result and $arg (which will be passed to bind_result)
		$result = array();
		$arg = array($handle);
		for ($i=0; $i < $nof; $i++) {
		    $result[$i] = '';
		    $arg[$i+1] = &$result[$i];
		}
		
		call_user_func_array ('mysqli_stmt_bind_result',$arg);
		
		
		$myall = array();
		
		# after mysqli_stmt_fetch(), our result array is filled just perfectly,
		# but it is numbered (like in mysql_fetch_array() ), not indexed by field name!
		# Make it ordered by field name.
		while($handle->fetch()) {
			 $row = array();
			
			 for ($i=0; $i < $nof; $i++) {
			 	$row[$fields[$i]] = $result[$i];
			 }
			 $myall[] = $row;
		}
		return $myall;
	}
}
?>
