<?php

/*
 * Created on Apr 5, 2007
 */
class RegnSession {
	private $db;

	function __construct($db) {
		$this->db = $db;
 
		if(!$db->table_exists("sessions")) {
            $query = 'CREATE TABLE sessions (
                  SessionID     char(255)   not null,
                  LastUpdated   datetime    not null,
                  DataValue     text,
                  PRIMARY KEY ( SessionID ),
                  INDEX ( LastUpdated )
                 )';
            $this->db->action($query);
        }
        session_set_save_handler(
        	array($this,"sessao_open"), 
			array($this,"sessao_close"), 
			array($this,"sessao_read"), 
			array($this,"sessao_write"), 
			array($this,"sessao_destroy"), 
			array($this,"sessao_gc"));
	}

	function sessao_open($aSavaPath, $aSessionName) {
       global $aTime;

       return TRUE;
    }

    function sessao_close() {
       return TRUE;
    }

    function sessao_read( $aKey ) {
	
	   $prep = $this->db->prepare("SELECT DataValue FROM sessions WHERE SessionID=?");
       
       $prep->bind_param("s", $aKey);
       
       $res = $this->db->execute($prep);
       
       if(sizeof($res) > 0) {
           return $res[0]['DataValue'];
       } else {
		   $prep = $this->db->prepare       	
             ("INSERT INTO sessions (SessionID, LastUpdated, DataValue)
                       VALUES (?, NOW(), '')");
           $prep->bind_param("s", $aKey);
           
           $this->db->execute($prep);
           return "";
       }
	}

	function sessao_write( $aKey, $aVal ) {
       $prep = $this->db->prepare       	
             ("UPDATE sessions SET DataValue = ?, LastUpdated = NOW() WHERE SessionID = ?");
       $prep->bind_param("ss", $aVal, $aKey);
       $this->db->execute($prep);
       return TRUE;
	}

	function sessao_destroy( $aKey ) {
       $prep = $this->db->prepare       	
             ("DELETE FROM sessions WHERE SessionID = ?");
       $prep->bind_param("s", $aKey);
       $this->db->execute($prep);
       return TRUE;
 	}

	function sessao_gc( $aMaxLifeTime ) {
       $prep = $this->db->prepare       	
             ("DELETE FROM sessions WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(LastUpdated) > ?");
       $prep->bind_param("i", $aMaxLifeTime);
       $this->db->execute($prep);
       return TRUE;
	}

		
    function session_redirect ($url = "") {
        function _safe_set (&$var_true, $var_false = "") {
            if (!isset ($var_true))
            { $var_true = $var_false; }
        }

        $parse_url = parse_url ($url);
        _safe_set ($parse_url["scheme"], "http");
        _safe_set ($parse_url["host"], $_SERVER['HTTP_HOST']);
        _safe_set ($parse_url["path"], "");
        _safe_set ($parse_url["query"], "");
        _safe_set ($parse_url["fragment"], "");
       
        if (substr ($parse_url["path"], 0, 1) != "/")
        {
            $parse_url["path"] = dirname ($_SERVER['PHP_SELF']) .
                           "/" . $parse_url["path"];
        }
       
        if ($parse_url["query"] != "") { 
        	$parse_url["query"] = $parse_url["query"] . "&amp;"; 
        }
        
        $parse_url["query"] = "?" . $parse_url["query"] .
                         session_name () . "=" .
                        strip_tags (session_id ());
       
        if ($parse_url["fragment"] != "")
        { $parse_url["fragment"] = "#" . $parse_url["fragment"]; }
       
        $url = $parse_url["scheme"] . "://" . $parse_url["host"] .
             $parse_url["path"] . $parse_url["query"] .
             $parse_url["fragment"];
       
        session_write_close ();
        header ("Location: " . $url);
        exit;     
    }
}
?>
