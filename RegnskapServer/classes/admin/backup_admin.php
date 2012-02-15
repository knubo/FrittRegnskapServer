<?php

class BackupAdmin {
    private $db;

    function BackupAdmin($db) {
        if (!$db) {
            $db = new DB();
        }

        $this->db = $db;
    }

    public static function upload($prefix, $_FILES) {

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

        return BackupAdmin::analyze($files);
    }

    private static function deleteAllFiles($dir) {
        $files = glob($dir . "/*");

        foreach ($files as $one) {
            if ($one == '.' || $one == '..') {
                continue;
            }

            unlink($one);
        }
    }

    private static function analyze($files) {
        $result = array();

        foreach ($files as $one) {
            $data = file_get_contents($one);

            $deleteNotFound = stristr($data, "delete from") === FALSE;
            $dropNotFound = stristr($data, "drop table") === FALSE;
            $truncateNotFound = stristr($data, "truncate") === FALSE;

            $tableName = basename($one, ".sql");

            $lockFound = !(strstr($data, "LOCK TABLES `$tableName` WRITE;") === FALSE);
            $insertFound = !(strstr($data, "INSERT INTO `$tableName`") === FALSE);
            $unlockFound = !(strstr($data, "UNLOCK TABLES") === FALSE);

            $result[] = array("table"=> $tableName, "deleteNotFound" => $deleteNotFound, "dropNotFound" => $dropNotFound,
                "truncateNotFound" => $truncateNotFound, "lockFound" => $lockFound,
                "insertFound" => $insertFound, "unlockFound" => $unlockFound);
        }

        return $result;
    }

    public static function viewFile($prefix, $viewFile) {
        readfile("../../storage/$prefix/admin_backup/$viewFile.sql");
    }

}