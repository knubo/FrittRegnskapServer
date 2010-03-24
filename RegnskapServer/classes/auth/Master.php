<?php class Master {

    private $db;

    function Master($db) {
        $this->db = $db;
    }

    
    function calculate_prefix() {
        return "regn_";
    }


}

?>
