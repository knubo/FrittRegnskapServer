<?php
echo "Svar:";

$username = 'knutbo@ifi.uio.no';
$password = 'Rotibus42';
$urlToLoginTo = 'https://www.domeneshop.no/admin.cgi';


$opts = array('http' =>
array('method'  => 'Get',
	'Header' => "User-Agent: Fritt Regnskap Knut Erik Borgen\r\n",
    'user_agent' => 'Fritt Regnskap admin@frittregnskap.no'
)
);

$context  = stream_context_create($opts);
$data = file_get_contents($urlToLoginTo, false, $context);

$hits = array();
preg_match("/session=(.+?)\"/", $data, &$hits);

$session = $hits[1];

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
		'Header' => "User-Agent: Fritt Regnskap Knut Erik Borgen\r\n".
                    "Cookie	language=no; currency=NOK; sessionid=$session",
    	'user_agent' => 'Fritt Regnskap admin@frittregnskap.no',
        'Cookie' => "Cookie	language=no; currency=NOK; sessionid=$session",
                'content' => $postdata
)
);

$context  = stream_context_create($opts);

$fp = fopen($urlToLoginTo.'?session=$session', 'r', false, $context);
fpassthru($fp);
fclose($fp);
?>