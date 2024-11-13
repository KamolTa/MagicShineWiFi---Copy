<?php
include "lib/connect_db.php";
date_default_timezone_set("Asia/Bangkok");

include "configs.php";

$username = $_GET["username"];
$mac = trim($_GET["mac"]);

//Get mac from cookies
if(isset($_COOKIE["c_mac"])) {
    $mac = trim($_COOKIE["c_mac"]);
}
if($mac != "")
    setcookie("c_mac", $mac, time() + 10 * 365 * 86400);

$uptime = $_GET["uptime"];
$time_left = $_GET["time_left"];
$r_time = $_GET["r_time"];
$server_name = $_GET["server_name"];

$datetime = new DateTime();
$dt_str = $datetime->format('Y-m-d H:i:s');
$sql = "SELECT update_count FROM user_mac_status WHERE username='$username' AND mac = '$mac' ";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$update_count = trim($row["update_count"]);

//usage log
$conn->query("INSERT into usage_log (username, mac, status, log_time) VALUES ('$username', '$mac', '1', '$dt_str')");

$station_mac = shell_exec("ifconfig eth0 | grep -Eo ..\(\:..\){5}");
$station_mac = trim(strtoupper($station_mac));

if ($update_count == "")
{
    $post = [
        'username' => $username,
        'mac' => $mac,
        'station_mac' => $station_mac,
        'login_status' => '1'
    ];
    $ch = curl_init("$WIFI_server/set_usage_log");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $status = curl_exec($ch);
    curl_close($ch);

    if($status == "Y")
    {
        $sql = "INSERT into user_mac_status (username, mac, update_time, update_count) VALUES ('$username', '$mac', '$dt_str', '1') ";
        $result = $conn->query($sql);

        $sql = "INSERT into first_login_log (username, login_time) VALUES ('$username', '$dt_str') ";
        $result = $conn->query($sql);
    }
}
else
{
    if($update_count > 5) {
        $update_count = 0;
        $post = [
            'username' => $username,
            'mac' => $mac,
            'station_mac' => $station_mac,
            'login_status' => '1'
        ];
        $ch = curl_init("$WIFI_server/set_usage_log");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $status = curl_exec($ch);
        curl_close($ch);
    }
    

    $update_count = $update_count + 1;
    $sql = "UPDATE user_mac_status SET update_time = '$dt_str', update_count = '$update_count' WHERE username = '$username' AND mac = '$mac' ";
    $result = $conn->query($sql);
}

$sql = "SELECT PLP.type, PLP.profile_name FROM users AS US, profile_login_prop AS PLP WHERE US.username = '$username' AND US.profile = PLP.profile_name";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
if($row["type"] === "LS")
{
    header("Location:http://www.google.co.th");
}

$package = $row["profile_name"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>MagicShine WiFi</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
  <!-- Bootstrap core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="css/mdb.min.css" rel="stylesheet">
  <!-- Your custom styles (optional) -->
  <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="offset-sm-1 offset-md-4 col-sm-10 col-md-4">
                <p class="text-center font-weight-bold">Logged in to MagicShine WiFi</p>
                <table class="table table-bordered">
                    <tbody>
                        <tr><td class="text-center font-weight-bold" style="width:50%;">Package</td><td class="text-center"><?php echo $package; ?></td></tr>
                        <tr><td class="text-center font-weight-bold">Username</td><td class="text-center"><?php echo $username; ?></td></tr>
                        <tr><td class="text-center font-weight-bold">Time Left</td><td class="text-center"><?php echo $time_left; ?></td></tr>
                    </tbody>
                </table>
                <p class="text-center">Logout at <a href='<?php echo $MKGateway;?>/logout'><?php echo $MKGateway;?>/logout</a></p>
                <!--p class="text-center">Check Status at <a href='<?php echo $MKGateway;?>/status'>here</a></p-->
            </div>
        </div>
    </div>
<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
  <!-- Bootstrap tooltips -->
  <script type="text/javascript" src="js/popper.min.js"></script>
  <!-- Bootstrap core JavaScript -->
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <!-- MDB core JavaScript -->
  <script type="text/javascript" src="js/mdb.js"></script>
</body>

</html>
