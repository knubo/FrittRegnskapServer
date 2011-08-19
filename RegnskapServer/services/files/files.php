<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/util/fileutil.php");
include_once ("../../classes/accounting/accountline.php");

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$file = array_key_exists("file", $_REQUEST) ? $_REQUEST["file"] : "";
$includeYears = array_key_exists("years", $_REQUEST) ? $_REQUEST["years"] : "0";

$prefix = "";
if (AppConfig::USE_QUOTA) {
    $prefix = $regnSession->getPrefix() . "/";
}

switch ($action) {
    case "list":

        if (!is_dir("../../storage/$prefix")) {
            mkdir("../../storage/$prefix",0700, true);
        }

        $directory = dir("../../storage/$prefix");

        $res = array();
        $total = 0;
        while (false !== ($data = $directory->read())) {
            if ($data[0] != '.') {
                if (is_dir("../../storage/$prefix/$data")) {
                    $size = dirSize("../../storage/$prefix/$data");
                    $total += $size;
                    $res[] = array("name" => $data, "size" => Strings::formatBytes($size), "link" => 0);

                } else {
                    $size = filesize("../../storage/$prefix/$data");
                    $total += $size;
                    $res[] = array("name" => $data, "size" => Strings::formatBytes($size), "link" => 1);
                }
            }
        }

        $percentUsed = 0;

        if (AppConfig::USE_QUOTA) {
            $percentUsed = sprintf("%01.2f", (($total / ($regnSession->getQuota() * 1024 * 1024)) * 100));
        }

        $arr = array("files" => $res,
                     "totalsize" => Strings::formatBytes($total),
                     "quota" => $regnSession->getQuota() . " MB",
                     "used" => $percentUsed);

        if ($includeYears == 1) {
            $acLine = new AccountLine($db);
            $arr["years"] = $acLine->listUniqeYears();
        }

        echo json_encode($arr);

        break;
    case "image":


        if ($file[0] == '.') {
            echo "0";
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: image');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize("../../storage/" . $prefix . "/" . $file));
            ob_clean();
            flush();
            readfile("../../storage/" . $prefix . "/" . $file);
        }

        break;
    case "deltext":
        if ($file[0] == '.') {
            echo "0";
        } else {

            $filename = "../../storage/" . $prefix . "/txt/" . $file;

            if(!file_exists($filename)) {
                echo json_encode(array("status" => 2));
                break;
            }

            unlink($filename);
            echo json_encode(array("status" => 1));
        }
        break;


    case "gettext":
        if (!is_dir("../../storage/$prefix/txt")) {
            mkdir("../../storage/$prefix/txt",0700, true);
        }

        if ($file[0] == '.') {
            echo "0";
        } else {

            $filename = "../../storage/" . $prefix . "/txt/" . $file;

            if(!file_exists($filename)) {
                echo "\n";
                break;
            }

            readfile($filename);
        }
        break;
    case "get":
        if ($file[0] == '.') {
            echo "0";
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $file);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize("../../storage/" . $prefix . "/" . $file));
            ob_clean();
            flush();
            readfile("../../storage/" . $prefix . "/" . $file);
        }

        break;

    case "delete":
        $res = array();


        if ($file[0] == '.') {
            $result["status"] = 0;
        } else {
            $res["result"] = unlink("../../storage/" . $prefix . $file) ? 1 : 0;
            $logger->log("info", "files", "Deleted: $fileName");
        }

        echo json_encode($res);

        break;
    case "writetext":
        $regnSession->checkWriteAccess();

        if (!is_dir("../../storage/$prefix/txt")) {
            mkdir("../../storage/$prefix/txt",0700, true);
        }

        $status = file_put_contents("../../storage/$prefix/txt/".$_REQUEST["file"], $_REQUEST["data"]);

        echo json_encode(array("status" => $status));
        break;


    case "upload":
        $regnSession->checkWriteAccess();

        $fileName = $_FILES['uploadFormElement']['name'];

        $fileName = Strings::whitelist($fileName);

        $result = array();

        $prefix = "";
        $percentUsed = 0;

        if (AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix() . "/";

            if (!is_dir("../../storage/$prefix")) {
                mkdir("../../storage/$prefix",0700, true);
            }

            $directory = dir("../../storage/$prefix");

            $total = 0;
            while (false !== ($data = $directory->read())) {
                if ($data[0] != '.') {
                    if (is_dir("../../storage/$prefix/$data")) {
                        $size = dirSize("../../storage/$prefix/$data");
                    } else {
                        $size = filesize("../../storage/$prefix/$data");
                    }
                    $total += $size;
                }
            }

            $total += filesize($_FILES['uploadFormElement']['tmp_name']);

            $percentUsed = sprintf("%01.2f", (($total / ($regnSession->getQuota() * 1024 * 1024)) * 100));

        }

        if ($percentUsed > 100) {
            $result["status"] = -1;
        } else if ($fileName[0] == '.') {
            $result["status"] = 0;
        } else {
            $result["status"] = copy($_FILES['uploadFormElement']['tmp_name'], "../../storage/" . $prefix . $fileName)
                    ? 1 : 0;
        }

        unlink($_FILES['uploadFormElement']['tmp_name']);

        $logger->log("info", "files", "Uploaded: $fileName");

        echo json_encode($result);
        break;
}

?>