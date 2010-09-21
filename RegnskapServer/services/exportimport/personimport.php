<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/import/personimportclass.php");
include_once ("../../classes/import/personimportpersisterclass.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$delimiter = array_key_exists("delimiter", $_REQUEST) ? $_REQUEST["delimiter"] : "";
$exclude = array_key_exists("exclude", $_REQUEST) ? $_REQUEST["exclude"] : "";
$db = new DB(1);
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();



switch($action) {
    case "findfields":
        $content = Strings::file_get_contents_utf8($_FILES['uploadFormElement']['tmp_name']);

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
    case "preview":
        $pic = new PersonImportClass();
        $pic->parseContents($exclude, $delimiter);
        break;
    case "insert":
        $pic = new PersonImportPersisterClass($db);
        $pic->parseContents($exclude, $delimiter);
        break;
}


?>