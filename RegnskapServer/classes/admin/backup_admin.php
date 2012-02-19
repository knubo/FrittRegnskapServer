<?php

class BackupAdmin {
    private $db;

    function BackupAdmin($db) {
        if (!$db) {
            $this->db = new DB();
        }

        $this->db = $db;
    }

    public function uploadAndAnalyze($prefix, $_FILES) {

        if (!is_dir("../../storage/$prefix")) {
            mkdir("../../storage/$prefix", 0700, true);
        }

        if (!is_dir("../../storage/$prefix/admin_backup")) {
            mkdir("../../storage/$prefix/admin_backup", 0700, true);
        }

        BackupAdmin::deleteAllFiles("../../storage/$prefix/admin_backup");

        $file = $_FILES['uploadFormElement']['tmp_name'];

        $cmd = "/usr/bin/unzip -j $file -d ../../storage/$prefix/admin_backup";

        $data = array();
        exec($cmd, &$data);

        $files = glob("../../storage/$prefix/admin_backup/*");

        return $this->analyze($files);
    }

    private function deleteAllFiles($dir) {
        $files = glob($dir . "/*");

        foreach ($files as $one) {
            if ($one == '.' || $one == '..') {
                continue;
            }

            unlink($one);
        }
    }

    private function analyze($files) {
        $result = array();

        foreach ($files as $one) {
            $data = Strings::file_get_contents_utf8($one);

            $deleteNotFound = stristr($data, "delete from") === FALSE;
            $dropNotFound = stristr($data, "drop table") === FALSE;
            $truncateNotFound = stristr($data, "truncate") === FALSE;

            $tableName = Strings::whitelist(basename($one, ".sql"));

            $lockFound = !(strstr($data, "LOCK TABLES `$tableName` WRITE;") === FALSE);
            $insertFound = !(strstr($data, "INSERT INTO `$tableName`") === FALSE);
            $unlockFound = !(strstr($data, "UNLOCK TABLES") === FALSE);

            $tableExist = $this->db->table_exists($tableName);

            $backupExist = $this->db->table_exists($tableName . "_backup");

            $result[] = array("table" => $tableName, "deleteNotFound" => $deleteNotFound, "dropNotFound" => $dropNotFound,
                "truncateNotFound" => $truncateNotFound, "lockFound" => $lockFound,
                "insertFound" => $insertFound, "unlockFound" => $unlockFound,
                "tableExist" => $tableExist, "backupExist" => $backupExist);
        }

        return $result;
    }

    public static function viewFile($prefix, $viewFile) {
        readfile("../../storage/$prefix/admin_backup/$viewFile.sql");
    }

    public function backupTable($table) {

        if (!$this->db->table_exists($table) || !$this->db->table_exists($table . "_backup")) {
            return -1;
        }
        $this->db->copyTable($table, $table . "_backup");

        $prep = $this->db->prepare("select count(*) as c from $table" . "_backup");
        $res = $prep->execute();

        return $res[0]["c"];
    }

    public function deleteAndinstallFromBackup($masterprefix, $table) {

        if(!$this->db->table_exists($table)) {
            return -1;
        }
        $this->db->action("delete from $table");

        $sqls = Strings::file_get_contents_utf8("../../storage/$masterprefix/admin_backup/$table.sql");

        $statements = explode("\n", $sqls);

        foreach ($statements as $one) {
            if ($one && strlen(chop($one)) > 0 && strncmp($one, "/*", 2) != 0 && strncmp($one, "--",2 != 0)) {
                $this->db->action($one);
            }
        }
        $this->db->action("ALTER TABLE $table AUTO_INCREMENT = 1");

        $prep = $this->db->prepare("select count(*) as c from $table");
        $res = $prep->execute();

        return $res[0]["c"];

    }

}