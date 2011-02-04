<?php

class EmailArchive {

    private $db;

    function EmailArchive($db) {
        $this->db = $db;
    }

    function listAll() {
        $prep = $this->db->prepare("select id,username,sent,subject,edit_time, now() as now from " . AppConfig::pre() . "email_archive order by edit_time");

        return $prep->execute();
    }


    function getOne($id) {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "email_archive where id = ?");
        $prep->bind_params("i", $id);

        return array_shift($prep->execute());
    }

    function delete($id) {
        $prep = $this->db->prepare("delete from " . AppConfig::pre() . "email_archive where id = ?");
        $prep->bind_params("i", $id);

        $prep->execute();
    }

    function checkIfNeedToDelete($max) {

        $prep = $this->db->prepare("select count(*) as c from " . AppConfig::pre() . "email_archive");
        $data = $prep->execute();
        $count = $data[0]["c"];
        
        if($count <= $max) {
            return;
        }
        
        for($i = $max; $i < $count; $i++) {
            $prep = $this->db->prepare("select id from " . AppConfig::pre() . "email_archive where edit_time = (select min(edit_time) from " . AppConfig::pre() . "email_archive)");
            $res = $prep->execute();
            $id = $res[0]["id"];
            
            if(!$id) {
                /* Paranoia safeguard */
                return;
            }
            
            $prep = $this->db->prepare("delete from " . AppConfig::pre() . "email_archive where id = ?");
            $prep->bind_params("i", $id);
            $prep->execute();
        }


    }

    function saveOrUpdate($params, $max) {
        if(array_key_exists("id", $params)) {
            $prep = $this->db->prepare("update " . AppConfig::pre() . "email_archive set username=?,sent=?,subject=?,body=?, edit_time=now(),footer=?,header=?, format=? where id=?");
            $prep->bind_params("sisssssi", $params["username"], $params["sent"], urldecode($params["subject"]), urldecode($params["body"]), $params["footer"], $params["header"], $params["format"], $params["id"]);
            $prep->execute();
            return $params["id"];
        }

        $prep = $this->db->prepare("insert into " . AppConfig::pre() . "email_archive set username=?,sent=?,subject=?, edit_time=now(), body=?, footer=?, header=?, format=?");
        $prep->bind_params("sissiis", $params["username"], $params["sent"], urldecode($params["subject"]), urldecode($params["body"]),$params["footer"], $params["header"], $params["format"]);
        $prep->execute();

        $insertId = $this->db->insert_id(); 
        
        $this->checkIfNeedToDelete($max);

        return $insertId;

         
    }

}

?>