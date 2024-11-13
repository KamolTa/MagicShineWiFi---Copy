<?php
include "lib/connect_db.php";
$username = $_GET['username'];
$user_num = $_GET['user_num'];
echo $user;
$sql = "UPDATE users SET max_user_num = '$user_num' WHERE username='$username'";
$conn->query($sql);

?>