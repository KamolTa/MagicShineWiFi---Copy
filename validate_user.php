<?php
require('lib/routeros_api.class.php');
include("configs.php");

$user = $_POST["username"];
$pass = $_POST["password"];
$mac = $_POST["mac"];

$post = [
    'username' => $user,
    'password' => $pass
];

$ch = curl_init("$WIFI_server/get_user_profile");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
$gae = trim(curl_exec($ch));

curl_close($ch);

if($gae == "E")
{
    $API = new RouterosAPI();
    if ($API->connect($MKTWanIP, 'admin', 'enigma'))
    {
        $ARRAY = $API->comm('/tool/user-manager/user/print', array(".proplist" => ".id", "?username" => $user));
        $response = $API->comm("/tool/user-manager/user/remove", array(".id" => $ARRAY[0]['.id']));
    }
    $API->disconnect();

    echo "E";
    exit();
}



include "lib/connect_db.php";

//Check if user exists in Raspberry Pi
$sql = "SELECT profile, max_user_num FROM users WHERE username = '$user' AND password = '$pass'  ";
$result = $conn->query($sql);

//T success
//P no matcing profile
//FL full user num
//N No data
$user_status = "";
if($result->num_rows > 0)
{
    $row = $result->fetch_assoc();
    $profile = $row["profile"];
    $max_user = $row["max_user_num"];

    $sql = "SELECT * FROM profile_login_prop WHERE profile_name = '$profile' ";
    $result = $conn->query($sql);
    if($result->num_rows === 0)
    {
        //Error No matching profile
        echo "P";
        exit();
    }
    $row = $result->fetch_assoc();
    $type = $row["type"];
    $max_user_num = $row["max_user_num"];

    if($max_user_num < $max_user)
      $max_user_num = $max_user;
      
    if($type === "LS")
    {
        $sql = "SELECT username FROM user_mac_status WHERE username = '$user' AND mac = '$mac'";
        $result = $conn->query($sql);
        $row_count = $result->num_rows;

        $sql = "SELECT username FROM user_mac_status WHERE username = '$user'";
        $result = $conn->query($sql);
        $row_count_total = $result->num_rows;

        if($row_count > 0)
        {
            $user_status = "T";
        }
        else if($row_count_total < $max_user_num)
        {
            $user_status = "T";
        }
        else
        {
            echo "FL";
            exit();
        }

    }
    else
        $user_status = "T";

    if ($user_status == "T")
    {
        setcookie("user", $user, time()+(10*365*24*60*60));
        setcookie("pass", $pass, time()+(10*365*24*60*60));
        WriteUserToMikroTik(new RouterosAPI(), $MKTWanIP, "admin", "enigma", "admin", $user, $pass, $profile);
        echo "T";
        exit();
    }
}

//Check if user exists in gae
$post = [
    'username' => $user,
    'password' => $pass
];

$ch = curl_init("$WIFI_server/get_user_profile");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
$profile = trim(curl_exec($ch));

curl_close($ch);

if($profile == "E")
{
    $API = new RouterosAPI();
    if ($API->connect($MKTWanIP, 'admin', 'enigma'))
    {
        $ARRAY = $API->comm('/tool/user-manager/user/print', array(".proplist" => ".id", "?username" => $user));
        $response = $API->comm("/tool/user-manager/user/remove", array(".id" => $ARRAY[0]['.id']));
    }
    $API->disconnect();

    echo "E";
    exit();
}
else if($profile == "N" || $profile == "" || strlen($profile) > 10)
{
    echo "N";
    exit();
}

setcookie("user", $user, time()+(10*365*24*60*60));
setcookie("pass", $pass, time()+(10*365*24*60*60));

//$API->debug = true;
WriteUserToMikroTik(new RouterosAPI(), $MKTWanIP, "admin", "enigma", "admin", $user, $pass, $profile);

$sql = "INSERT into users (username, password, profile) VALUES ('$user', '$pass', '$profile')  ";
$result = $conn->query($sql);

echo "T";
exit();


function WriteUserToMikroTik($API, $MKTWanIP, $MKTUsername, $MKTPassword, $MKTCustomer, $username, $password, $profile)
{
    if ($API->connect($MKTWanIP, $MKTUsername, $MKTPassword)) {

        $ARR = $API->comm("/tool/user-manager/user/add", Array(
            "customer" => $MKTUsername,
            "username" => $username,
            "password" => $password));

        $ARRAY = $API->comm('/tool/user-manager/user/print', array(".proplist" => "username,customer,actual-profile", "?username" => "$username"));
        $u = trim($ARRAY[0]["username"]);
        $c = trim($ARRAY[0]["customer"]);
        $p = trim($ARRAY[0]["actual-profile"]);

        if ($u == $username && $c == $MKTCustomer && $p != $profile)
        {
            $ARR = $API->comm("/tool/user-manager/user/create-and-activate-profile", Array(
                "numbers" => $username,
                "customer" => $MKTCustomer,
                "profile" => $profile));
        }
    }

    $API->disconnect();
}

?>
