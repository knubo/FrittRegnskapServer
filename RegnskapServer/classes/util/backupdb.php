<?php
class BackupDB {

	private $db;
    private $logger;
    private $prefix;
    public $info;
    
	function BackupDB($db,$prefix) {
		$this->db = $db;
		$this->prefix = $prefix;
        $this->logger = new Logger($db);
	}

	function info() {
	}

	function init() {

	}

	function tables() {
		$tables = array ();

		$this->addTables(AppConfig::pre(), &$tables);

		if($this->prefix == "master_") {
		    $this->addTables("wikka_", &$tables);
            $tables[] = "sqllist";
            $tables[] = "to_install";
            $tables[] = "installations";
		}
		
		return $tables;
	}

	function addTables($prefix, $tables) {
		$prep = $this->db->prepare("show tables like '$prefix%'");
		$res = $prep->execute();

		foreach ($res as $value) {
			$tables = array_merge($tables, array_values($value));
		}
	    
	}
	
    function zip() {
        $cmd = "/usr/bin/zip ../backup/".$this->prefix."/backup.zip ../backup/".$this->prefix."/*.sql";

        $data = array();
        $res = exec($cmd, &$data);

        if(count($data) == 0) {
            $this->logger->log("error","command", "Failed to run command $cmd");
            return false;
        }
        return true;
    }

	function backup($table) {
	    
	    $dbinfo = AppConfig::db();

        $cmd = AppConfig::MYSQLDUMP." -cnt -u " . $dbinfo[1] . " -h " . $dbinfo[0];

        if($dbinfo[2] && strlen($dbinfo[2]) > 0) {
        	$cmd.= " -p" . $dbinfo[2];
        }

        $cmd.= " " . $dbinfo[3] . " $table";
        $data = array();
		$res = exec($cmd, &$data);

        if(count($data) == 0) {
            $this->logger->log("error","exec", "Failed to run command $cmd");
        	return 0;
        }
	    
        $this->info = array(count($data));
	    
        
        return file_put_contents("../backup/".$this->prefix."/$table.sql", implode("\n",$data)) > 0;
	}
}
?>