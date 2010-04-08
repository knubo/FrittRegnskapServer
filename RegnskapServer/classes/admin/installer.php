<?php

class Installer {

    private $db;


    function Installer($db) {
        $this->db = $db;
    }
    
    function dropTables($prefix) {
        $prep = $this->db->prepare("show tables like '$prefix%'");
        
        $res = $prep->execute();
        
        foreach($res as $one) {
            foreach(array_values($one) as $st) {
                $this->db->action("drop table $st");
            }
        }
    }

    function createTables($prefix) {
        $dbschema = file_get_contents("../../conf/dbschema.sql");

        $this->execute($dbschema, $prefix);
    }
    
    function createIndexes($prefix) {
        $indexes = file_get_contents("../../conf/indexes.sql");

        $this->execute($indexes, $prefix);
        
    }

    function addAccountPlan($prefix) {
        $posts = file_get_contents("../../conf/posts.sql");

        $this->execute($posts, $prefix);
    }
    
    function execute($sqls, $prefix) {
        $replaced = preg_replace("/XXX/",$prefix,$sqls);

        $statements = explode(";", $replaced);
        
        foreach ($statements as $one) {
            if($one && strlen(chop($one)) > 0) {
                $this->db->action($one);
            }
        }
        
    }
}



?>