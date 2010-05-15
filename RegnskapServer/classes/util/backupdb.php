<?php
class BackupDB {

	private $db;
    private $logger;

	function BackupDB($db) {
		$this->db = $db;
        $this->logger = new Logger($db);
	}

	function info() {
	}

	function init() {

	}

	function tables() {
		$prep = $this->db->prepare("show tables like '" . AppConfig::pre() . "%'");
		$res = $prep->execute();

		$tables = array ();
		foreach ($res as $value) {
			$tables = array_merge($tables, array_values($value));
		}

		return $tables;
	}

    function zip() {
        $cmd = "/usr/bin/zip ../backup/backup.zip ../backup/*.sql";

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
	    
        $cmd = AppConfig::MYSQLDUMP." -cnt -u" . $dbinfo[1] . " -h " . $dbinfo[0];

        if($dbinfo[2] && strlen($dbinfo[2]) > 0) {
        	$cmd.= " -p" . $dbinfo[2];
        }

        $cmd.= " " . $dbinfo[3] . " $table";
        $data = array();
		$res = exec($cmd, &$data);

        if(count($data) == 0) {
            $this->logger->log("error","exec", "Failed to run command $cmd");
        	return false;
        }

        return file_put_contents("../backup/$table.sql", implode("\n",$data)) > 0;
	}
}
?>