<?php

class AccountLine {
  public $Id;
  public $Description;
  public $Attachment;
  public $Postnmb;
  private $Occured;
  private $db;

  /*! Used in getMonth */
  private $Posts;
  public $PostsArray;
  public $groupDebetMonth;
  public $groupKredMonth;
  public $date;
  
  function AccountLine($db, $postnmb = 0, $attachment = 0, $description = 0, $day = 0, $id=0, $occured = 0) {
	$this->db = $db;
    $this->Postnmb = $postnmb;
    $this->Attachment = $attachment;
    $this->Description = $description;

    if($occured) {
      $this->Occured = $occured;
      $this->date = $occured->displayAccount();

    } else if($day) {
      $standard = new AccountStandard($db);
      $month = $standard->getOneValue("STD_MONTH");
      $year = $standard->getOneValue("STD_YEAR");

      $this->Occured =& new eZDate();
      $this->Occured->setDay($day);
      $this->Occured->setMonth($month);
      $this->Occured->setYear($year);

    }
    $this->Id = $id;
  }

  function readLineAndPosts($ids) {
	
	$params = implode(",", array_fill(0, sizeof($ids), "?"));
	$prep = $this->db->prepare("select L.id, L.attachnmb, L.occured, L.postnmb, L.description, P.debet, P.post_type, P.amount, PT.description as postdesc FROM regn_line L, regn_post P, regn_post_type PT where L.id = P.line and L.id IN (".$params.") and PT.post_type = P.post_type order by L.id, P.debet");

	$prep->bind_array_params(str_repeat("i", sizeof($ids)), $ids);

	return $prep->execute($prep);
  }


  function read($id) {
    $prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description from regn_line where id = ?");
	$prep->bind_params("i", $id);
	$line_query = $prep->execute($prep);

    foreach($line_query as $one) {
      $this->Id = $id;
      $this->Attachment = $one["attachnmb"];
      $this->Occured = new eZDate();
      $this->Occured->setMySQLDate($one["occured"]);
      $this->date = $this->Occured->displayAccount();
      $this->Postnmb = $one["postnmb"];
      $this->Description = $one["description"];
    }
  }

  function sumOneLinePosts($lineId, $debkred) {
	$prep = $this->db->prepare("select sum(regn_post.amount) as s from regn_post where line=? and debet=?");
	$prep->bind_params("is", $lineId, $debkred);
	$result_lines = $prep->execute($prep);
	
    if(count($result_lines) == 0) {
      return 0;
    }
    
    return $result_lines[0]["s"];

  }

  function sumPostsYear($posttype, $year, $debkred) {
	$prep = $this->db->prepare("select sum(regn_post.amount) as s from regn_post, regn_line ".
         "WHERE regn_post.post_type=? and regn_post.debet=? ".
         "and regn_line.year=? ".
         "and regn_line.id = regn_post.line");
         
    $prep->bind_params("isi", $posttype, $debkred, $year);
    $result_lines = $prep->execute($prep);

    if(count($result_lines) == 0) {
      return 0;
    }
    
    return $result_lines[0]["s"];

  }

  function sumPosts($posttype, $year, $month, $debkred) {
	$prep = $this->db->prepare("select sum(regn_post.amount) as s from regn_post, regn_line ".
         "WHERE regn_post.post_type=? and regn_post.debet=? ".
         "and regn_line.year=? and regn_line.month=? ".
         "and regn_line.id = regn_post.line");
    $prep->bind_params("isii",$posttype, $debkred, $year, $month);
	$result_lines = $prep->execute();
	
    if(count($result_lines) == 0) {
      return 0;
    }
    
    return $result_lines[0]["s"];
  }


  function setNewLatest($description, $day, $year, $month) {
    $this->Description = $description;

    $this->Occured =& new eZDate();
    $this->Occured->setDay($day);
    $this->Occured->setMonth($month);
    $this->Occured->setYear($year);
    
    $this->Postnmb = $this->getNextPostnmb($year, $month);
    $this->Attachment = $this->getNextAttachmentNmb($year);
  }

  function update() {
  	$prep = $this->db->prepare("update regn_line set attachnmb = ?, postnmb = ?, occured = ?, description = ? where id = ?");
  		
  	$prep->bind_params("iissi", $this->Attachment, $this->Postnmb, $this->Occured->mySQLDate(), $this->Description,$this->Id);
		  	
	$prep->execute();
  }

  function updateAttachment($id, $attachment) {
	$prep = $this->db->prepare("update regn_line set attachnmb = ? where id = ?");
	
	$prep->bind_params("ii", $attachment, $id);  	
	$prep->execute();
  }
  
  function store() {

    $standard = new AccountStandard($this->db);
    $month = $standard->getOneValue("STD_MONTH");
    $year = $standard->getOneValue("STD_YEAR");
    
    $prep = $this->db->prepare("insert into regn_line SET id=null,attachnmb=?,postnmb=?,description=?,month=?,year=?,occured=?");
    $prep->bind_params("iisiis", $this->Attachment, $this->Postnmb, $this->Description, $month, $year, $this->Occured->mySQLDate());
    $prep->execute();
    $this->Id = $this->db->insert_id();
  }

  function addPostDebKred($line, $post_type, $amount) {
    $post = new AccountPost($this->db, $line, "1", $post_type, $amount);
    $post->store();

    $post = new AccountPost($this->db, $line, "-1", $post_type, $amount);
    $post->store();

  }

  function addPostSingleAmount($line, $debet, $post_type, $amount, $project = 0, $person = 0) {

    $post = new AccountPost($this->db, $line, $debet, $post_type, $amount, 0, $project, $person);

    $post->store();

  }
  function addPost($line, $debet, $post_type, $nok, $nokdes, $project = 0, $person = 0) {
    $amount = "";
    if($nokdes) {
      $amount = "$nok.$nokdes";
    } else {
      $amount  = "$nok.00";
    }

    $this->addPostSingleAmount($line, $debet, $post_type, $amount, $project, $person);
  }

  function getMonthSimple($month) {
    $standard = new AccountStandard($this->db);
    $year = $standard->getOneValue("STD_YEAR");

	$prep = $this->db->prepare("select id, attachnmb, occured, description from regn_line where year = ? and month = ? order by postnmb");
	$prep->bind_params("ii", $month, $year);

	$line_query = $prep->execute();
    
    $res = array();

    foreach($line_query as $one) {
  	   $occured = new eZDate();
   	   $occured->setMySQLDate($one["occured"]);
	   $res[] = new AccountLine($this->db, 0, $one["attachnmb"], $one["description"], 0, $one["id"], $occured);
    }
    return $res;
  }


  /*! Returns all posts in a month. On the accountlines that are
    returned, the posts are cached for better access */
  function getMonth($year, $month, $fromline = 0, $toline = 0, $fillGrouped = 0) {

    $query = 0;
	$prep = 0;
	
    if($fromline && $toline) {
    	$prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description from regn_line where month=? and year=? and id >= ? and id <= ? order by postnmb");
    	$prep->bind_params("iiii", $year, $month, $fromline, $toline);
    } else {
    	$prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description from regn_line where month=? and year=? order by postnmb");
    	$prep->bind_params("ii", $month, $year);
    }

    $lines = $this->getLines($prep->execute());
    
    if($fillGrouped) {
    	$this->fill_grouped($lines);
    }
    
    return $lines;
  }
  /*!
   * Removes the postArray data here as I don't want it when requesting overview data.
   * Sums up the collection post data making it ready for wire.
   */
  function fill_grouped($lines) {
  	  foreach($lines as $one) {
	
		 $one->groupDebetMonth = array();
		 $one->groupKredMonth = array(); 	 
  	  	 
  	  	 foreach(array_keys($one->Posts) as $groupid) {
  	  	 	 $posts = $one->Posts[$groupid];
  	  	 	 
  	  	 	 foreach($posts as $post) {
  	  	 	 	if($post->getDebet() == "1") {
 					if(array_key_exists($groupid, $one->groupDebetMonth)) {
  	  	 	 		   $one->groupDebetMonth[$groupid] += $post->getAmount();
 					} else {
  	  	 	 		   $one->groupDebetMonth[$groupid] = $post->getAmount(); 						
 					}
  	  	 	 	} else {
  	  	 	 		if(array_key_exists($groupid, $one->groupKredMonth)) {
  	  	 	 		   $one->groupKredMonth[$groupid] += $post->getAmount();  	  
  	  	 	 		} else {
  	  	 	 		   $one->groupKredMonth[$groupid] = $post->getAmount();  	    	  	 	 			
  	  	 	 		} 	 		
  	  	 	 	}
  	  	 	 }
  	  	 }
  	  	 $one->PostsArray = null;
  	  }
  }

  function getByKonto($year, $konto, $project, $person) {

	$params = array($year);
	$fields = "i";
	    
    $spec = "";

    if($konto) {
      $spec = "RP.post_type = ? AND ";
   	  $fields.="i";
   	  $params[] = $konto;
    }

    if($project) {
      $spec .= "RP.project = ? AND ";
   	  $fields.="i";
   	  $params[] = $project;
    }

    if($person) {
      $spec .= "RP.person = ? AND ";
   	  $fields.="i";
   	  $params[] = $person;
    }

    $prep = $this->db->prepare("select distinct(RL.id), RL.attachnmb, RL.occured, RL.postnmb, RL.description from regn_line RL, regn_post RP where RL.year=? AND $spec RP.line = RL.id order by RL.occured, RL.postnmb");
	$prep->bind_array_params($fields, $params);
	
    return $this->getLines($prep->execute());
  }

  /*! Expects a query done on regn_line*/
  function getLines($line_query) {

     $result_array = array();
     $sortedById = array();

     if( count( $line_query ) >= 0 ) {
      
        $accAccountPostType = new AccountPostType($this->db);

        /* Init cache in accAccountPostType */
        $accAccountPostType->getAll(1);      
      
        $minId = 999999999;
        $maxId = 0;
        foreach($line_query as $line) {

           $id = $line["id"];

	       $occured = new eZDate();
	       $occured->setMySQLDate($line["occured"]);
	       $day = $occured->day();

	       $line = new AccountLine($this->db, $line["postnmb"],
				  $line["attachnmb"],
				  $line["description"],
				  0,
				  $id,
				  $occured);
 	       $result_array[] = $line;
	       $sortedById[$id] = $line;
	
	      if($id < $minId) {
	         $minId = $id;
	      }
	      if($id > $maxId) {
	         $maxId = $id;
	      }
      }
      

      /* Then, fetch all posts based on min and max number. Assume
       * that most posts are registered linearly so we will not fetch
       * a lot of columns we do not need. And then we add the posts to
       * their respective lines */
      $accountPostAcc = new AccountPost($this->db);

      $allPosts = $accountPostAcc->getRange($minId, $maxId);

	
      foreach($allPosts as $onePost) {
	     $type = $onePost->getPost_type();
	     $collectionPost = $accAccountPostType->getAccountPostType($type);
	     if(array_key_exists($onePost->getLine(), $sortedById)) {
	        $sortedById[$onePost->getLine()]->addCachedPost($collectionPost->getCollectionPost(), $onePost);
	     }
      }
    }

    return $result_array;
  }

  function sumCashPosts($cashPosts) {
	  	
  	
    $postListString = implode(",", $cashPosts);

	$prep = $this->db->prepare("select sum(amount) as sum FROM regn_post WHERE".
      " line = ? AND debet='1' AND post_type IN ($postListString)");
	$prep->bind_params("i", $this->Id);

	$result_array = $prep->execute();

    $debet = 0;
    if(count($result_array) > 0) {
      $debet = $result_array[0]["sum"];
    }

	$prep = $this->prepare("select sum(amount) as sum FROM regn_post WHERE".
      " line = ? AND debet='-1' AND post_type IN ($postListString)");
      
	$prep->bind_params("i", $this->Id);

	$result_array = $prep->execute();

    $kredit = 0;
    if(count($result_array) > 0) {
      $kredit = $result_array[0]["sum"];
    }

    return $debet - $kredit;
  }


  function getNextAttachmentNmb($year) {
	
    $prep = $this->db->prepare("select max(attachnmb) as m from regn_line where year=?");
	$prep->bind_params("i", $year);
	
	$result_array = $prep->execute();

    if(count($result_array) == 1) {
      return$result_array[0]["m"] + 1;
    }
    return 0;

  }

  function getNextPostnmb($year, $month) {
  	
	$prep = $this->db->prepare("select max(postnmb) as m from regn_line where year=? and month=?");
	$prep->bind_params("ii", $year, $month);
	$result_array = $prep->execute();

    if(count($result_array) == 1) {
      return $result_array[0]["m"] + 5;
    }
    return 0;
  }


  /*! Returns the cached posts as an array */
  function getPosts() {
    return $this->PostsArray;
  }
  
  function fetchAllPosts() {
  	$this->postArray = $this->getAllPosts();
  }

  function addCachedPost($colposttype, $post) {
    if(!$this->Posts) {
      $this->Posts = array();
      $this->PostsArray = array();
    }
    if(!array_key_exists($colposttype, $this->Posts)) {
      $this->Posts[$colposttype] = array();
    }

    $this->Posts[$colposttype][] = $post;
    $this->PostsArray[] = $post;
  }

  /*! Returns all posts for a given account line. If first cached by
    getMonth, then the items are indexed by their collection post type. */
  function getAllPosts($onlycache = 0) {
    if($this->Posts) {
      return $this->Posts;
    }

    if($onlycache) {
      return array();
    }

    $accessor = new AccountPost($this->db, $this->Id);

    return $accessor->getAll($this->Id);
  }
  
  function search($desc) {

	$prep = $this->db->prepare("select id, attachnmb, occured, postnmb, description from regn_line where description like ?");
	$prep->bind_params("s", $desc);
	
	$line_query = $prep->execute();

    $result = array();
    foreach($line_query as $one) {
      $occ = new eZDate();
      $occ->setMySQLDate($one["occured"]);
      
      $result[] = new AccountLine($this->db, $one["postnmb"],
				    $one["attachnmb"],
				    $one["description"],
				    0,
				    $one["id"],
				    $occ);
    }
    return $result;
  }
  
  function getId() {
    return $this->Id;
  }

  function getDescription() {
    return $this->Description;
  }
  function getAttachment() {
    return $this->Attachment;
  }
  function getPostnmb() {
    return $this->Postnmb;
  }
  
  function getDay() {
    return $this->Occured->day();
  }

  function getOccured() {
    return $this->Occured->displayAccount();
  }  
}

?>
