<?php
include_once("../../conf/AppConfig.php");

/*
 * Created on Apr 5, 2007
 */

class RegnSession {
	private $db;
				
	function __construct($db) {
		$this->db = $db;

		if(!mysql_table_exists("sessions",$db)) {
            $query = 'CREATE TABLE sessions (
                  SessionID     char(255)   not null,
                  LastUpdated   datetime    not null,
                  DataValue     text,
                  PRIMARY KEY ( SessionID ),
                  INDEX ( LastUpdated )
                 )';
            $db->action($query);
        }
        session_set_save_handler("sessao_open", "sessao_close", "sessao_read", "sessao_write", "sessao_destroy", "sessao_gc");
	}	
		
                            

	function sessao_open($aSavaPath, $aSessionName) {
       global $aTime;

       sessao_gc( $aTime );
       return TRUE;
    }

    function sessao_close() {
       return TRUE;
    }

    function sessao_read( $aKey ) {
	
	   $aKey = mysql_escape_string($aKey);
	   
       $query = "SELECT DataValue FROM sessions WHERE SessionID='$aKey'";
       $busca = mysql_query($query);
       if(mysql_num_rows($busca) == 1)
       {
             $r = mysql_fetch_array($busca);
             return $r['DataValue'];
       } else {
             $query = "INSERT INTO sessions (SessionID, LastUpdated, DataValue)
                       VALUES ('$aKey', NOW(), '')";
             mysql_query($query);
             return "";
       }
	}

	function sessao_write( $aKey, $aVal ) {
       $aVal = mysql_escape_string( $aVal );
       $query = "UPDATE sessions SET DataValue = '$aVal', LastUpdated = NOW() WHERE SessionID = '$aKey'";
       mysql_query($query);
       return TRUE;
	}

	function sessao_destroy( $aKey ) {
       $aKey = mysql_escape_string( $aKey );				
       $query = "DELETE FROM sessions WHERE SessionID = '$aKey'";
       mysql_query($query);
       return TRUE;
 	}

	function sessao_gc( $aMaxLifeTime ) {
    	$aMaxLifeTime = mysql_escape_string($aMaxLifeTime);
       $query = "DELETE FROM sessions WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(LastUpdated) > $aMaxLifeTime";
       mysql_query($query);
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
