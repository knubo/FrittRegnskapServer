<?php

/*
 * Created on Apr 5, 2007
 */
class RegnSession {
    private $db;
    private $prefix;

    function auth() {

        if(!AppConfig::USE_AUTHENTICATION) {
            return;
        }

        session_start();

        if(!$_SESSION["username"]) {
            header("HTTP/1.0 510 Not Authenticated :".json_encode($_SESSION));
            die("Not authenticated");
        }

        /* Usage logging */
        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/RegnskapServer/ACTIVITY.log",gmdate("d.m.Y-H:i:s",$_SERVER["REQUEST_TIME"])." ".$_SERVER["SERVER_NAME"]." ".$_SERVER["REMOTE_HOST"]." ".$_SESSION["username"]." ".basename($_SERVER["SCRIPT_NAME"])." ".$_REQUEST["action"]."\n", FILE_APPEND);

        return $_SESSION["username"];
    }

    function projectRequired() {
        if(!AppConfig::USE_AUTHENTICATION) {
            return 0;
        }
        return $_SESSION["project_required"];
    }

    function getPersonId() {
        if(!AppConfig::USE_AUTHENTICATION) {
            return 0;
        }

        return $_SESSION["person_id"];
    }

    function getSuperDBIfAny() {
        if(!array_key_exists("parentdb", $_SESSION)) {
            return 0;
        }

        return $_SESSION["parentdb"];
    }

    function getSuperDBPrefix() {
        if(!array_key_exists("parentdbprefix", $_SESSION)) {
            return 0;
        }

        return $_SESSION["parentdbprefix"];
    }

    function getReducedMode() {
        if(!array_key_exists("reduced_mode", $_SESSION)) {
            return 0;
        }

        return $_SESSION["reduced_mode"];
    }

    function getArchiveMax() {
        if(!array_key_exists("archive_limit", $_SESSION)) {
            return 2;
        }

        $m = $_SESSION["archive_limit"];

        if($m) {
            return $m;
        }

        return 2;
    }

    function getQuota() {
        if(!AppConfig::USE_AUTHENTICATION || !AppConfig::USE_QUOTA) {
            return 0;
        }

        return $_SESSION["diskquota"];
    }

    function getPrefix() {
        if(!AppConfig::USE_AUTHENTICATION) {
            return "regn_";
        }

        return $_SESSION["prefix"];
    }


    function canSeeSecret() {
        if(!AppConfig::USE_AUTHENTICATION) {
            return 1;
        }

        return $_SESSION["can_see_secret"];

    }

    function hasReducedWriteAccess() {
        if(!AppConfig::USE_AUTHENTICATION) {
            return 0;
        }

        return $_SESSION["reducedwrite"];
    }

    function checkReducedWriteAccess() {
        if(!AppConfig::USE_AUTHENTICATION) {
            return;
        }

        if($_SESSION["reducedwrite"]) {
            return;
        }
         
        if($_SESSION["readonly"]) {
            header("HTTP/1.0 511 No access");
            die("No access for operation");
        }
    }

    function checkWriteAccess() {
        if(!AppConfig::USE_AUTHENTICATION) {
            return;
        }

        if($_SESSION["readonly"]) {
            header("HTTP/1.0 511 No access");
            die("No access for operation");
        }
         
    }


    function RegnSession($db, $prefix = 0, $sessionName = 0) {
        $this->db = $db;

        if($sessionName) {
            session_name($sessionName);
        }

        if(!$prefix) {
            $masterDB = new DB(0, DB::MASTER_DB);
            $master = new Master($masterDB);
            $masterRecord = $master->get_master_record();
            $prefix = $masterRecord["dbprefix"];
        }

        $this->prefix = $prefix;

        if(!$db->table_exists($prefix ."sessions")) {
            $query = 'CREATE TABLE '.$prefix .'sessions (
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

        $prep = $this->db->prepare("SELECT DataValue FROM ".$this->prefix ."sessions WHERE SessionID=?");
         
        $prep->bind_params("s", $aKey);
         
        $res = $prep->execute($prep);
         
        if(sizeof($res) > 0) {
            return $res[0]['DataValue'];
        } else {
            $prep = $this->db->prepare
            ("INSERT INTO ".$this->prefix ."sessions (SessionID, LastUpdated, DataValue)
                       VALUES (?, NOW(), '')");
            $prep->bind_params("s", $aKey);
             
            $prep->execute($prep);
            return "";
        }
    }

    function sessao_write( $aKey, $aVal ) {
        $prep = $this->db->prepare
        ("UPDATE ".$this->prefix ."sessions SET DataValue = ?, LastUpdated = NOW() WHERE SessionID = ?");
        $prep->bind_params("ss", $aVal, $aKey);
        $prep->execute($prep);
        return TRUE;
    }

    function sessao_destroy( $aKey ) {
        $prep = $this->db->prepare
        ("DELETE FROM ".$this->prefix ."sessions WHERE SessionID = ?");
        $prep->bind_params("s", $aKey);
        $prep->execute($prep);
        return TRUE;
    }

    function sessao_gc( $aMaxLifeTime ) {
        $prep = $this->db->prepare
        ("DELETE FROM ".$this->prefix ."sessions WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(LastUpdated) > ?");
        $prep->bind_params("i", $aMaxLifeTime);
        $prep->execute($prep);
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

    function allSessions() {
        $prep = $this->db->prepare("select LastUpdated, DataValue from ".$this->prefix."sessions order by LastUpdated");
        return $prep->execute();
    }


    function setSessionVariablesForAdmin($masterRecord) {
        $_SESSION["username"] = "fradmin";
        $_SESSION["readonly"] = 0;
        $_SESSION["reducedwrite"] = 0;
        $_SESSION["project_required"] = 0;
        $_SESSION["person_id"] = 0;
        $_SESSION["can_see_secret"] = 1;

        $_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];
         
        $_SESSION["prefix"] = $masterRecord["dbprefix"];
        $_SESSION["diskquota"] = $masterRecord["diskquota"];
        if($masterRecord["archive_limit"]) {
            $_SESSION["archive_limit"] = $masterRecord["archive_limit"];
        } else {
            $_SESSION["archive_limit"] = 2;
        }
        if($masterRecord["reduced_mode"]) {
            $_SESSION["reduced_mode"] = $masterRecord["reduced_mode"];
        } else {
            $_SESSION["reduced_mode"] = 0;
        }
        if($masterRecord["parentdbprefix"]) {
            $_SESSION["parentdbprefix"] = $masterRecord["parentdbprefix"];
        } else {
            $_SESSION["parentdbprefix"] = 0;
        }
    }

    function setSessionVarialbesForUser($user, $auth, $masterRecord) {

        $_SESSION["username"] = $user;
        $_SESSION["readonly"] = $auth->hasOnlyReadAccess();
        $_SESSION["reducedwrite"] = $auth->hasReducedWrite();
        $_SESSION["project_required"] = $auth->hasProjectRequired();
        $_SESSION["person_id"] = $auth->getPersonId();
        $_SESSION["can_see_secret"] = $auth->canSeeSecret();
        $_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];

        $_SESSION["prefix"] = $masterRecord["dbprefix"];
        $_SESSION["diskquota"] = $masterRecord["diskquota"] > 0 ? $masterRecord["diskquota"] : 5;
        if($masterRecord["archive_limit"]) {
            $_SESSION["archive_limit"] = $masterRecord["archive_limit"];
        } else {
            $_SESSION["archive_limit"] = 2;
        }
        if($masterRecord["reduced_mode"]) {
            $_SESSION["reduced_mode"] = $masterRecord["reduced_mode"];
        } else {
            $_SESSION["reduced_mode"] = 0;
        }
        if($masterRecord["parentdbprefix"]) {
            $_SESSION["parentdbprefix"] = $masterRecord["parentdbprefix"];
        } else {
            $_SESSION["parentdbprefix"] = 0;
        }

        $_SESSION["parentdb"] = $masterRecord["parenthostprefix"] ? DB::dbhash($masterRecord["parenthostprefix"]) : 0;
    }


}
?>
