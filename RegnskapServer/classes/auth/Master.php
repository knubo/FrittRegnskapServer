<?php class Master {

    public $db;

    function Master($db) {
        $this->db = $db;
    }

    function getAllInstallations() {
        $prep = $this->db->prepare("select * from installations");

        return $prep->execute();
    }


    function get_master_record() {
        /* Do not understand this bug, why is this needed?... */
        if(!$this->db) {
            $this->db = new DB();
        }
        $prep = $this->db->prepare("select * from installations where hostprefix = ?");

        $host = $_SERVER["SERVER_NAME"];
        
        $split = explode(".",$host);

        if(strlen($split[0]) < 2 || $split[0] == "localhost") {
            $split[0] = "php5";
        } 
        
        $prep->bind_params("s", $split[0]);

        $res = $prep->execute();

        if(count($res) > 0) {
            return $res[0];
        }

        /* Default to BSC as of now */
        return array("dbprefix" => "regn_", "default"=>true, "diskquota"=>42);
    }


}

?>
