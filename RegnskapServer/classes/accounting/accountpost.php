<?
include_once( "classes/ezdb.php" );


class eZAccountPost {
  var $Id;
  var $Line;
  var $Debet;
  var $Post_type;
  var $Amount;
  var $Project;
  var $Person;

  function eZAccountPost($line =0, $debet=0, $post_type=0, $amount=0, $id = 0, $project = 0, $person = 0) {
    $this->Line = $line;
    $this->Debet = $debet;
    $this->Post_type = $post_type;
    $this->Amount = $amount;
    $this->Project = $project;
    $this->Person = $person;

    $this->Id = $id;
  }

  function getProject() {
    return $this->Project;
  }

  function getPerson() {
    return $this->Person;
  }

  function getId() {
    return $this->Id;
  }
  function getLine() {
    return $this->Line;
  }
  function getDebet() {
    return $this->Debet;
  }
  function getPost_type() {
    return $this->Post_type;
  }
  function getAmount() {
    return $this->Amount;
  }
  
  function store() {
    $this->dbInit();

    $line = addslashes($this->Line);
    $debet = addslashes($this->Debet);
    $post_type = addslashes($this->Post_type);
    $amount = addslashes($this->Amount);
    $person = addslashes($this->Person);
    $project = addslashes($this->Project);

    if(!$person) {
      $person = 0;
    }
    if(!$project) {
      $project = 0;
    }

    $this->Database->query("insert into regn_post set id=null, line=$line, debet='$debet',post_type=$post_type, amount=$amount, person=$person, project=$project");
  }

  function getRange($start, $stop) {
    $this->dbInit();

    $this->Database->array_query( $group_array, "SELECT * FROM regn_post where line >= $start and line <= $stop" );

    return $this->filled_result($group_array);    
  }

  function getAll($parent) {
    $this->dbInit();

    $this->Database->array_query( $group_array, "SELECT * FROM regn_post where line=$parent" );
    
    return $this->filled_result($group_array);
  }
  
  function filled_result($group_array) {
    $return_array = array();

    if( count( $group_array ) >= 0 ) {
      for( $i=0; $i < count ( $group_array ); $i++ ) {
	$return_array[$i] = 
	  new eZAccountPost($group_array[$i]["line"],
			    $group_array[$i]["debet"],
			    $group_array[$i]["post_type"],
			    $group_array[$i]["amount"],
			    $group_array[$i]["id"],
			    $group_array[$i]["project"],
			    $group_array[$i]["person"]
			    );
      }
    }
    return $return_array;
  }

  function delete($lineId, $postId) {
    $this->dbInit();

    $l = addslashes($linedId);    
    $p = addslashes($postId);    
    
    $this->Database->query("delete from regn_post where line=$lineId and id=$postId");
  }

  function dbInit() {
    if ( $this->IsConnected == false ) {
      $this->Database = eZDB::globalDatabase();
      $this->IsConnected = true;
    }
  }


  
}

?>
