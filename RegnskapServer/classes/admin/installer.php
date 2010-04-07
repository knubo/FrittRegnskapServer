<?php

class Installer {

    private $db;


    function Installer($db) {
        $this->db = $db;
    }
    
    function dropTables($prefix) {
    }

    function createTables($prefix) {
        $dbschema = file_get_contents("../../conf/dbschema.sql");

        $this->execute($dbschema);
    }
    
    function createIndexes($prefix) {
        $indexes = file_get_contents("../../conf/indexes.sql");

        $this->execute($indexes);
        
    }

    function addAccountPlan($prefix) {
        $posts = file_get_contents("../../conf/posts.sql");

        $this->execute($posts);
    }
    
    function execute($sqls) {
        $replaced = preg_replace("/exists regn_/","exists ".$prefix,$sqls);

        $statements = explode(";", $replaced);
        
        foreach ($statements as $one) {
            if($one && strlen(chop($one)) > 0) {
                $this->db->action($one);
            }
        }
        
    }
}



?>