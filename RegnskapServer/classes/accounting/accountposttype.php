<?

include_once( "classes/ezdb.php" );

class eZAccountPostType {

  var $PostType;
  var $CollPost;
  var $Description;
  var $DetailPost;
  var $InUse;
  var $AllEntries;

  function eZAccountPostType($a = 0, $b = 0, $c = 0, $d = 0, $f = 0) {
    $this->IsConnected = false;
        
    $this->PostType =& $a;
    $this->CollPost =& $b;
    $this->Description =& $c;
    $this->DetailPost =& $d;
    $this->InUse =& $f;
  }

  function getInUse() {
    return $this->InUse;
  }

  function getPosttype() {
    return $this->PostType;
  }

  function getCollectionPost() {
    return $this->CollPost;
  }

  function getDescription() {
    return $this->Description;
  }
  
  function getDetailPost() {
    return $this->DetailPost;
  }

  function getSomeIndexedById($ids) {
    $p = $this->getSome($ids);
    
    $answer = array();

    foreach($p as $one) {
      $answer[$one->getPosttype()] = $one;
    }
    return $answer;
  }

  function store() {
    $this->dbInit();

    $safePostType = addslashes($this->PostType);
    $safeCollPost = addslashes($this->CollPost);
    $safeDescription = addslashes($this->Description);
    $safeDetailPost = addslashes($this->DetailPost);
    
    $this->Database->query("insert into regn_post_type (post_type, coll_post, detail_post, description, in_use) values ($safePostType, $safeCollPost, $safeDetailPost, '$safeDescription', 1)");    
  }

  function getSome($ids, $from = 0 , $to = 0) {
    $this->dbInit();

    $return_array = array();

    $where = 0;

    if($from && $to) {
      $where = "SELECT * FROM regn_post_type WHERE post_type >= $from and post_type <= $to order by post_type";
    } else {
      $where = "SELECT * FROM regn_post_type where post_type IN (".
	implode($ids,",").")";
    }

    $this->Database->array_query( $group_array, $where);

    if( count( $group_array ) >= 0 ) {

      for( $i=0; $i < count ( $group_array ); $i++ ) {

	$pt = $group_array[$i]["post_type"];

	$one =
	  new eZAccountPostType($pt,
				$group_array[$i]["coll_post"],
				$group_array[$i]["description"],
				$group_array[$i]["detail_post"]
				);
	$return_array[$i] = $one;
      }
    }

    return $return_array;
  }

  function getAllFordringer() {
    $ini = new INIFIle( "site.ini" );
    
    $postIds = $ini->read_array("eZAccountMain", "FordringPosts");
    
    return $this->getSome($postIds);
  }

  function getAll($disableFilter = 0) {

    $this->dbInit();

    $return_array = array();

    $this->AllEntries = array();

    $q = 0;

    if($disableFilter) {
      $q = "SELECT * FROM regn_post_type order by in_use DESC, post_type, description";
    } else {
      $q = "SELECT * FROM regn_post_type where in_use = 1 order by description";
    }

    $this->Database->array_query( $group_array, $q);

    if( count( $group_array ) >= 0 ) {

      for( $i=0; $i < count ( $group_array ); $i++ ) {

	$pt = $group_array[$i]["post_type"];

	$one =
	  new eZAccountPostType($pt,
				$group_array[$i]["coll_post"],
				$group_array[$i]["description"],
				$group_array[$i]["detail_post"],
				$group_array[$i]["in_use"]

				);
	$return_array[$i] = $one;
	$this->AllEntries[$pt] = $one;
      }
    }

    return $return_array;
  }

  /*! Call this only after you have fethced all posttypes */
  function getAccountPostType($id) {
    return $this->AllEntries[$id];
  }

  function getYearEndTransferPost() {
    $ini = new INIFIle( "site.ini" );
    
    return $ini->read_var("eZAccountMain", "EndPostYearTransferPost");
  }

  function aktiver($posts) {
    $this->dbInit();

    $this->Database->query("update regn_post_type set in_use = 1 where post_type IN(".
			   implode(",", $posts).")");
    
  }

  function slett($posts) {
    $this->dbInit();

    $this->Database->query("update regn_post_type set in_use = 0 where post_type IN(".
			   implode(",", $posts).")");
    
  }


  function getEndTransferPost() {
    $ini = new INIFIle( "site.ini" );
    
    return $ini->read_var("eZAccountMain", "EndPostTransferPost");

  }

  function getEndPosts() {
    $ini = new INIFIle( "site.ini" );
    
    return $ini->read_array("eZAccountMain", "EndPosts");
  }

  /*! Private function. Open the database for read and write. Gets
    all the database information from site.ini.  */

  function dbInit() {
    if ( $this->IsConnected == false ) {
      $this->Database = eZDB::globalDatabase();
      $this->IsConnected = true;
    }
  }  
}
?>
