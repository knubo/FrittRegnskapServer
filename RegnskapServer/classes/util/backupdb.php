<?php
class BackupDB {

    private $db;
    private $logger;
    private $prefix;
    public $info;

    function BackupDB($db, $prefix) {
        $this->db = $db;
        $this->prefix = $prefix;
        $this->logger = new Logger($db);
    }

    function info() {
    }

    function init() {

    }

    function tables() {
        $tables = array();

        $this->addTables(AppConfig::pre(), &$tables);

        if ($this->prefix == "master_") {
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
            $one = array_shift(array_values($value));

            if (strpos($one, "_backup") === FALSE) {
                $tables[] = $one;
            }
        }

    }

    function zip() {
        $cmd = "/usr/bin/zip ../backup/" . $this->prefix . "/backup.zip ../backup/" . $this->prefix . "/*.sql";

        $data = array();
        $res = exec($cmd, &$data);

        if (count($data) == 0) {
            $this->logger->log("error", "command", "Failed to run command $cmd");
            return false;
        }
        return true;
    }

    function dump_plain($table) {
        $dbinfo = AppConfig::db();

        $cmd = AppConfig::MYSQLDUMP . "  --skip-opt -cntq -u " . $dbinfo[1] . " -h " . $dbinfo[0];

        if ($dbinfo[2] && strlen($dbinfo[2]) > 0) {
            $cmd .= " -p" . $dbinfo[2];
        }

        $cmd .= " " . $dbinfo[3] . " $table";
        $data = array();
        $res = exec($cmd, &$data);

        if (count($data) == 0) {
            $this->logger->log("error", "exec", "Failed to run command $cmd");
            return 0;
        }

        $patterns = array("/\/\*.*/", "/--.*/", "/`/");

        return trim(preg_replace($patterns, "", implode("\n", $data)));

    }

    function backup($table) {

        $dbinfo = AppConfig::db();

        $cmd = AppConfig::MYSQLDUMP . " -cnt -u " . $dbinfo[1] . " -h " . $dbinfo[0];

        if ($dbinfo[2] && strlen($dbinfo[2]) > 0) {
            $cmd .= " -p" . $dbinfo[2];
        }

        $cmd .= " " . $dbinfo[3] . " $table";
        $data = array();
        $res = exec($cmd, &$data);

        if (count($data) == 0) {
            $this->logger->log("error", "exec", "Failed to run command $cmd");
            return 0;
        }

        $this->info = array(count($data));


        return file_put_contents("../backup/" . $this->prefix . "/$table.sql", implode("\n", $data)) > 0;
    }
}

?>