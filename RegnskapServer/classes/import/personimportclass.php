<?php

class PersonImportClass {

    function startParsing() {
        echo "<table>\n";
    }
    function endParsing() {
        echo "</table>\n";
    }
    function startRow() {
        echo "<tr>\n";
    }
    function endRow($data) {
        echo "<tr>\n";
    }

    function oneColumn($value) {
        echo "<td>$value</td>";
    }

    function cleanData($field, $value) {
        return $value;
    }

    function parseContents($exclude, $delimiter) {
        $content = file_get_contents($_FILES['uploadFormElement']['tmp_name']);

        $lines = explode("\n", $content);

        $excludeList = explode(",", $exclude);

        $this->startParsing();

        $row = 0;
        foreach($lines as $one) {
            if(!(array_search($row, $excludeList) == FALSE)) {
                echo "SKIP";
                continue;
            }

            $matches = array();
            $one = preg_replace("/(\Q".$delimiter."\E)(?=(?:[^\"]|\"[^\"]*\")*$)/", "|#|", $one);
            $one = preg_replace("/\"/", "", $one);
            
            $matches = explode("|#|", $one);
            $colCount = count($matches);
            
            $this->startRow();

            $data = array();

            for($i = 0; $i < $colCount; $i++) {
                if(!array_key_exists("col$i", $_REQUEST)) {
                    continue;
                }
                $data[$_REQUEST["col$i"]] = $this->cleanData($_REQUEST["col$i"], $matches[$i]);

                $this->oneColumn($data[$_REQUEST["col$i"]]);

            }

            $this->endRow($data);
            $row++;
        }
        $this->endParsing();
    }
}

?>