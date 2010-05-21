<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$delimiter = array_key_exists("delimiter", $_REQUEST) ? $_REQUEST["delimiter"] : "";
$exclude = array_key_exists("exclude", $_REQUEST) ? $_REQUEST["exclude"] : "";
$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();



switch($action) {
    case "findfields":
        $content = file_get_contents($_FILES['uploadFormElement']['tmp_name']);

        $lines = explode("\n", $content);
         
        echo "<table class=\"importtable\">\n";

        $i = 0;
        $headers = 0;
        foreach($lines as $one) {

            if(!$headers) {
                $colCount = preg_match_all("/(\Q".$delimiter."\E)(?=(?:[^\"]|\"[^\"]*\")*$)/", $one, $matches);
                echo "<tr><td><!-- --></td>";

                for($col = 0; $col <= $colCount; $col++) {
                    echo "<td><div id=\"col".$col."\"><!-- --></div></td>";
                }
                echo "</tr>";

                $headers = 1;
            }

            $style = $i % 6 >= 3 ? "line1" : "line2";

            echo "<tr class=\"$style\"><td><div id=\"remove".$i."\"><!-- --></div></td><td>";
            $one = preg_replace("/(\Q".$delimiter."\E)(?=(?:[^\"]|\"[^\"]*\")*$)/", "</td><td>", $one);
            echo preg_replace("/\"/", "", $one);
            echo "</td></tr>\n";

            $i++;
            
        }
        echo "</table>\n";
         
        break;
    case "insert":
        $content = file_get_contents($_FILES['uploadFormElement']['tmp_name']);

        $lines = explode("\n", $content);

        $excludeList = explode(",", $exclude);
        
        $row = 0;
        foreach($lines as $one) {
            $matches = array();
            $colCount = preg_match_all("/(\Q".$delimiter."\E)(?=(?:[^\"]|\"[^\"]*\")*$)/", $one, &$matches);

            if(!(array_search($row, $excludeList) === FALSE)) {
                continue;
            }
            
            $data = array();
            
            for($i = 0; $i < $colCount; $i++) {
                if(!array_key_exists("col$i", $_REQUEST)) {
                    continue;
                }
                $data[$_REQUEST["col$i"]] = $matches[$i];    
            }
            
            $row++;
        }
        
        break;
}


?>