<?php
include_once ("../../conf/AppConfig.php");

echo "Svar:";

$username = AppConfig::DOMENESHOP_USER;
$password = $_REQUEST["password"]; //AppConfig::DOMENESHOP_PASS;

$urlToLoginTo = 'https://www.domeneshop.no/admin.cgi';


$opts = array('http' =>
array('method'  => 'Get',
	'header' => "User-Agent: Fritt Regnskap Knut Erik Borgen\r\n",
    'user_agent' => 'Fritt Regnskap admin@frittregnskap.no'
)
);

$context  = stream_context_create($opts);
$data = file_get_contents($urlToLoginTo, false, $context);

$hits = array();
preg_match("/session=(.+?)\"/", $data, &$hits);

$session = $hits[1];

echo "<br>Using $session<br>";

$postdata = http_build_query(
array(
        'username' => $username,
        'password' => $password,
        'sessionid' => $session
)
);

$opts = array('http' =>
array(
        'method'  => 'POST',
		'header' => "User-Agent: Fritt Regnskap Knut Erik Borgen\r\n".
					"Cookie: sessionid=".$session."\r\n",
    	'user_agent' => 'Fritt Regnskap admin@frittregnskap.no',
                'content' => $postdata
)
);

$context  = stream_context_create($opts);

$fp = fopen($urlToLoginTo.'?session=$session', 'r', false, $context);

fpassthru($fp);
fclose($fp);


$opts = array('http' =>
array(
        'method'  => 'GET',
		'header' => "User-Agent: Fritt Regnskap Knut Erik Borgen\r\n".
					"Cookie: sessionid=".$session."\r\n",
    	'user_agent' => 'Fritt Regnskap admin@frittregnskap.no'
)
);

$context  = stream_context_create($opts);
$fp = fopen("https://www.domeneshop.no/admin.cgi?id=449465&edit=forwarding&session=".$session, 'r', false, $context);

fpassthru($fp);
fclose($fp);

/* Trying post */


$urlToAddDomain = "https://www.domeneshop.no/admin.cgi";
$postdata3 = http_build_query(
array(
        'session' => $session,
		'id' => "449465",
        'edit' => "forwarding",
        'host' => 'test2',
        'url' => "http://www.frittregnskap.no/",    
        'frame' => 'Y',
        'add.x' => "5",
        'add.y' => "5"
        ));

$opts3 = array('http' =>
array(
        'method'  => 'POST',
        'content_type' => 'application/x-www-form-urlencoded',
		'header' => "Host: www.domeneshop.no\r\n".
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n".
                    "Accept-Language: en-us,en;q=0.5\r\n".
                    "Accept-Encoding: gzip,deflate\r\n".
                    "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n".
                    "Referer: https://www.domeneshop.no/admin.cgi?id=449465&edit=forwarding\r\n".
					"Content-type: application/x-www-form-urlencoded\r\n".
					"User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12\r\n".
                    "Cookie: language=no; currency=NOK; sessionid=".$session."\r\n".
					"Content-Length: " . strlen($postdata) . "\r\n",
    	'user_agent' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
        'content' => $postdata3
)
);


$context3  = stream_context_create($opts3);
$fp3 = fopen($urlToAddDomain, 'r', false, $context3);

fpassthru($fp3);
fclose($fp3);

echo "used: $postdata3 <br>".json_encode($opts3);

?>