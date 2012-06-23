<?php
class PersonImportPersisterClass extends PersonImportClass {

    private $db;
    private $error = 0;
    private $rows = 0;

    function PersonImportPersisterClass($db) {
        $this->db = $db;
    }

    function startParsing() {
    }

    function endParsing() {
        echo "<p>Error count:" . $this->error . "<br/>\nRows inserted:" . $this->rows . "</p>";
    }

    function startRow($row) {
    }

    function cleanData($field, $value) {

        if ($field == "firstname" || $field == "lastname") {
            if ($value == "") {
                $this->error++;
                return NULL;
            }
        }


        if ($field == "birthdate") {
            if (preg_match("/\d\d?\.\d\d?\.\d\d\d\d/", $value) == 1) {
                $bdSave = new eZDate();
                $bdSave->setDate($value);
                return $bdSave->mySQLDate();
            }
            return NULL;
        }

        if ($field == "newsletter") {
            if ($value != 1 && $value != 0) {
                $this->error++;
                return NULL;
            }
        }

        if ($field == "year_membership_required" && value != 1 && value != 0) {
            $this->error++;
            return NULL;
        }

        if ($field == "semester_membership_required" && value != 1 && value != 0) {
            $this->error++;
            return NULL;
        }

        if ($field == "gender") {
            if ($value == "M" || $value == "m" || $value == "mann" || $value == "male") {
                return "M";
            }
            if ($value == "F" || $value == "K" || $value == "f" || $value == "k" || $value == "kvinne" || $value == "female") {
                return "F";
            }

            $this->error++;
            return NULL;
        }

        if (!$value) {
            return "";
        }

        return $value;
    }

    function endRow($data) {
        $this->db->begin();

        if (array_key_exists("membership_required_year", $data)) {
            $data["year_membership_required"] = $data["membership_required_year"];
            unset($data["membership_required_year"]);
        }
        if (array_key_exists("membership_required_semester", $data)) {
            $data["semester_membership_required"] = $data["membership_required_semester"];
            unset($data["membership_required_semester"]);
        }

        $keys = array_keys($data);

        $values = array();
        $types = "";
        foreach ($keys as $one) {
            $types .= $this->getType($one);
            $values[] = $data[$one];

            if ($data[$one] === NULL) {
                $this->db->rollback();
                return;
            }
        }


        $sql = "insert into " . AppConfig::pre() . "person (" . join(",", $keys) . ") values (" . join(",", array_fill(0, count($keys), "?")) . ")";

        $prep = $this->db->prepare($sql);
        $prep->bind_array_params($types, $values);
        $prep->execute();

        $this->rows++;

        $this->db->commit();
    }

    function getType($one) {
        switch ($one) {
            case "firstname":
            case "lastname":
            case "address":
            case "cellphone":
            case "birthdate":
            case "phone":
            case "country":
            case "city":
            case "gender":
            case "postnmb":
            case "comment":
            case "email":
                return "s";
            case "newsletter":
            case "year_membership_required":
            case "semester_membership_required":
                return "i";
            default:
                die("Unknown column sent in: $one");
        }
    }

    function oneColumn($value) {
    }

}