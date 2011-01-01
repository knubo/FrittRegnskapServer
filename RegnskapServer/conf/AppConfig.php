<?php

class AppConfig {

    function db($dbselect = 0) {

        if(1) {
            return array("localhost","root","","bsc_kopi");
        }

        $host = $_SERVER["SERVER_NAME"];

        $split = explode(".",$host);


        if($split[0] == 'localhost') {
            return array("localhost","root","","bsc_kopi");
        }
        if($split[0] == 'bsc') {
            return array("","","","");
        }

        return array("","","","");

    }
    
    const DOMENESHOP_USER = "knutbo@ifi.uio.no";
    const DOMENESHOP_PASS = "";
    

    const DB_HOST_NAME="localhost";
    const DB_USER="root";
    const DB_PASSWORD="";
    const DB_NAME="bsc_kopi";

    #Set to 1 if you want authentication.
    const USE_AUTHENTICATION=1;

    const USE_QUOTA=1;

    const TIMEZONE="Europe/Oslo";

    #Set to 1 if you want to validate email using checkdnsrr - some systems might not support it.
    const VALIDATE_EMAIL_USING_CHECKDNSRR=0;

    const MYSQLDUMP="/usr/local/mysql/bin/mysqldump";

    const CONVERT = "export PATH=\$PATH:/opt/local/bin;convert";

    const WIKKA_PREFIX = "wikka2_";
    
    function pre() {
        if(!$_SESSION) {
            return "regn_";            
        }
        return $_SESSION["prefix"];
    }

    const ABSOLUTE_URL_TO_SERVICES = "/RegnskapServer/services/";

    const LOG_DB_STATEMENTS = false;

    #Values for count. Must match CountCoulumns.
    function CountValues() {
        return array(1000,500,200,100,50,20,10,5,1,0.5);
    }

    #Columns in database for count
    function CountColumns() {
        return array('a1000','a500','a200','a100','a50','a20','a10','a5','a1','a_5');
    }

    #Fordring posts
    function FordringPosts() {
        return array(1370,1380,1390,1500,1570);
    }
    #Posts that are to be transfered to next month.
    function EndPosts() {
        return array(1904,1905,1906, 1920,1921);
    }

    #Posts available in select when registering a membership.
    function RegisterMembershipPosts() {
        return array(1920,1905, 2910, 2990);
    }

    const RELATIVE_PATH_MAIN_SERVICES = "../../../RegnskapServer";
    
    #Fond - club account post
    const ClubAccountPost=1920;
    #
    const BBC_FondDebetPost=7795;
    #
    const BBC_FondKreditPost=3995;
    #
    const TSO_FondKreditPost=3397;
    #
}

/* Disable magic quotes */
if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

?>
