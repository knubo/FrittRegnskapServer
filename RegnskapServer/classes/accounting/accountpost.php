<?php
include_once( "../util/DB.php" );


class eZAccountPost {
  private $Id;
  private $Line;
  private $Debet;
  private $Post_type;
  private $Amount;
  private $Project;
  private $Person;

  function eZAccountPost($db, $line =0, $debet=0, $post_type=0, $amount=0, $id = 0, $project = 0, $person = 0) {
    $this->db = $db;
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

	$prep = $this->db->prepare("insert into regn_post set id=null, line=?, debet=?,post_type=?, amount=?, person=?, project=?");
	
	$prep->bind_params("isiiii", $this->Line, $this->Debet, $this->Post_type, $this->Amount, $this->Person, $this->Project);

	$prep->execute();
  }

  function getRange($start, $stop) {
	$prep = $this->db->prepare("SELECT * FROM regn_post where line >= ? and line <= ?");
	$prep->bind_params("ii", $start, $stop);

    return $this->filled_result($prep->execute());    
  }

  function getAll($parent) {
	$prep = $this->db->prepare("SELECT * FROM regn_post where line=?");
	$prep->bind_params("i", $parent);
	
    return $this->filled_result($prep->execute());
  }
  
  function filled_result($group_array) {
    $return_array = array();

    if( count( $group_array ) >= 0 ) {
      for( $i=0; $i < count ( $group_array ); $i++ ) {
	    $return_array[$i] = 
	       new eZAccountPost($this->db, $group_array[$i]["line"],
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
	$prep = $this->db->prepare("delete from regn_post where line=? and id=?");
	$prep->bind_params("ii", $lineId, $postId);
  }
}

?>
