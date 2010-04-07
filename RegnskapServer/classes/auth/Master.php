<?php class Master {

    public $db;

    function Master($db) {
        $this->db = $db;
    }

    function getAllInstallations() {
        $prep = $this->db->prepare("select * from installations");

        return $prep->execute();
    }


    function calculate_prefix() {
        /* Do not understand this bug, why is this needed?... */
        if(!$this->db) {
            $this->db = new DB();
        }
        $prep = $this->db->prepare("select dbprefix from installations where hostprefix = ?");

        $host = $_SERVER["SERVER_NAME"];
        
        $split = explode(".",$host);
        
        $prep->bind_params("s", $split[0]);

        $res = $prep->execute();

        if(count($res) > 0) {
            return $res[0]["dbprefix"];
        }

        /* Default to BSC as of now */
        return "regn_";
    }


}

?>
