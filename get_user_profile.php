<?php

//Check if user exists in radius server
$post = [
    'username' => "60000",
    'password' => "41255"
];

$ch = curl_init("https://magicshinewifi.appspot.com/get_user_profile");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
$profile = curl_exec($ch);

curl_close($ch);

echo $profile;

?>