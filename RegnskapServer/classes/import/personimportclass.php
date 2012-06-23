<?php

class PersonImportClass {
    private $errorCount = 0;

    function startParsing() {
        echo "<table class=\"importtable\">\n";
    }
    function endParsing() {
        echo "</table>\n";
        echo "<p>Error count was:".$this->errorCount."</p>\n";
    }
    function startRow($row) {
        $style = $row % 6 >= 3 ? "line1" : "line2";

        echo "<tr class=\"$style\">\n";
    }
    function endRow($data) {
        echo "<tr>\n";
    }

    function oneColumn($value) {
        if($value == "###ERROR###") {
            echo "<td class=\"error\">$value</td>";
        } else {
            echo "<td>$value</td>";
        }

        return $value;
    }

    function cleanData($field, $value) {

        if($field == "firstname" || $field == "lastname") {
            if($value == "") {
                $this->errorCount++;
                return "###ERROR###";
            }
        }

        if($field == "birthdate") {
            if(preg_match("/\d\d?\.\d\d?\.\d\d\d\d/", $value) == 0) {
                $this->errorCount++;
                return "###ERROR###";
            }
        }

        if($field == "gender" && $value != "M" && $value != "F" && $value != "K" &&  $value != "mann" && $value != "kvinne" && $value != "mann" && $value != "kvinne" && $value != "male" && $value != "female") {
            $this->errorCount++;
            return "###ERROR###";
        }

        if($field == "newsletter" && $value != 1 && $value != 0) {
            $this->errorCount++;
            return "###ERROR###";
        }

        if($field == "membership_required_year" && value != 1 && value != 0) {
            $this->errorCount++;
            return "###ERROR###";
        }

        if($field == "membership_required_semester" && value != 1 && value != 0) {
            $this->errorCount++;
            return "###ERROR###";
        }

        return $value;
    }

    function parseContents($exclude, $delimiter) {
        $content = Strings::file_get_contents_utf8($_FILES['uploadFormElement']['tmp_name']);

        $lines = explode("\n", $content);

        if($exclude) {
            $excludeList = explode(",", $exclude);
        }

        $this->startParsing();

        $row = 0;
        foreach($lines as $one) {

            $one = trim($one);

            if(!$one) {
                continue;
            }

            if(!$excludeList || (array_search($row, $excludeList) === FALSE)) {

                $matches = array();
                $one = preg_replace("/(\Q".$delimiter."\E)(?=(?:[^\"]|\"[^\"]*\")*$)/", "|#|", $one);
                $one = preg_replace("/\"/", "", $one);

                $matches = explode("|#|", $one);
                $colCount = count($matches);

                $this->startRow($row);

                $data = array();

                for($i = 0; $i < $colCount; $i++) {
                    if(strlen($_REQUEST["col$i"]) == 0) {
                        continue;
                    }
                    $data[$_REQUEST["col$i"]] = $this->cleanData($_REQUEST["col$i"], $matches[$i]);

                    $this->oneColumn($data[$_REQUEST["col$i"]]);
                }

                $this->endRow($data);
            }

            $row++;
        }
        $this->endParsing();
    }
}

?>