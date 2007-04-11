<?
include_once( "classes/ezdb.php" );

class eZAccountStandard {
  
  function eZAccountStandard() {
    $this->IsConnected = false;
  }
  
  function setValue($id, $value) {
    $this->dbInit();
    
    $insId = addslashes($id);
    $insValue = addslashes($value);

    $this->Database->array_query($query_array, "select * from regn_standard where id='$insId'");

    if(count($query_array) > 0) {
      $this->Database->query("update regn_standard set value='$insValue' where id='$insId'");
    } else {
      $this->Database->query("insert into regn_standard (id, value) values ('$insId', '$insValue')");
    }
  }

  function getValue($id) {
    $this->dbInit();

    $qId = addslashes($id);
    $return_array = array();

    $this->Database->array_query($query_array, "select value from regn_standard where id='$qId'");
    if( count( $query_array ) >= 0 ) {
      for( $i=0; $i < count ( query_array ); $i++ ) {
	$return_array[$i] = $query_array[$i]["value"];
      }
    }

    return $return_array;    
  }

  function getOneValue($id) {
    $res = $this->getValue($id);

    if(count($res)) {
      return $res[0];
    }
  }


  function dbInit() {
    if ( $this->IsConnected == false ) {
      $this->Database = eZDB::globalDatabase();
      $this->IsConnected = true;
    }
  }

}

?>
