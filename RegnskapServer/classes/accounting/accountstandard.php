<?php
class AccountStandard {


    const CONST_YEAR = "STD_YEAR";
    const CONST_MONTH = "STD_MONTH";
    const CONST_SEMESTER = "STD_SEMESTER";
    const CONST_EMAIL_SENDER = "STD_EMAIL_SENDER";
    const CONST_MASSLETTER_DUE_DATE = "MASSLETTER_DUE_DATE";
    const CONST_BUDGET_YEAR_POST = "BDG_YEAR_POST";
    const CONST_BUDGET_COURSE_POST = "BDG_COURSE_POST";
    const CONST_BUDGET_TRAIN_POST = "BDG_TRAIN_POST";
    const CONST_BUDGET_YOUTH_POST = "BDG_YOUTH_POST";


    private $db;

    function AccountStandard($db) {
        $this->db = $db;
    }

    function setValue($id, $value) {

        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "standard where id=?");
        $prep->bind_params("s", $id);
        $query_array = $prep->execute();

        if (count($query_array) > 0) {
            $prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "standard set value=? where id=?");
            $prep->bind_params("ss", $value, $id);
            $prep->execute();

            return $this->db->affected_rows();
        } else {
            $prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "standard (id, value) values (?,?)");
            $prep->bind_params("ss", $id, $value);
            $prep->execute();

            return $this->db->affected_rows();
        }
    }

    function getValue($id) {
        $prep = $this->db->prepare("select value from " . AppConfig :: DB_PREFIX . "standard where id=?");
        $prep->bind_params("s", $id);

        $return_array = array ();

        $query_array = $prep->execute();
        if (count($query_array) >= 0) {
            for ($i = 0; $i < count($query_array); $i++) {
                $return_array[$i] = $query_array[$i]["value"];
            }
        }

        return $return_array;
    }

    function getOneValue($id) {
        $res = $this->getValue($id);

        if (count($res)) {
            return $res[0];
        }
    }
}
?>
