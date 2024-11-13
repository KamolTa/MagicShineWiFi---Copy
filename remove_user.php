<?php
require('lib/routeros_api.class.php');

$API = new RouterosAPI();

//$API->debug = true;

if($API->connect('192.168.43.20', 'admin', 'enigma') ) 
{
    
    $ARRAY = $API->comm('/tool/user-manager/user/print', array(".proplist" => ".id", "?username" => "10003"));
    $response = $API->comm("/tool/user-manager/user/remove",array(".id" => $ARRAY[0]['.id']));
}

$API->disconnect();


?>