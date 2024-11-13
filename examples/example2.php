<?php

require('../routeros_api.class.php');

$API = new RouterosAPI();

$API->debug = true;

if($API->connect('192.168.43.191', 'admin', 'enigma') ) 
{

$ARR=$API->comm("/tool/user-manager/user/add",Array( 
    "customer" => "admin",
    "username" => "php_user3",
    "password" => "php_password"));       


$ARR=$API->comm("/tool/user-manager/user/create-and-activate-profile",Array( 
    "numbers" => "php_user3",
    "customer" => "admin",
    "profile" => "TTProfile"));     
}


$API->disconnect();

?>
