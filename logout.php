<?php
include "lib/connect_db.php";
date_default_timezone_set("Asia/Bangkok");

include "configs.php";

$link_login = $_GET["link_login"];
$username = $_GET["username"];

$mac = trim($_GET["mac"]);

//Get mac from cookies
if(isset($_COOKIE["c_mac"])) {
    $mac = trim($_COOKIE["c_mac"]);
}

$uptime = $_GET["uptime"];
$time_left = $_GET["time_left"];

$datetime = new DateTime();
$dt_str = $datetime->format('Y-m-d H:i:s');

//Get station mac
$station_mac = shell_exec("ifconfig eth0 | grep -Eo ..\(\:..\){5}");
$station_mac = trim(strtoupper($station_mac));

/*$post = [
    'username' => $username,
    'mac' => $mac,
    'station_mac' => $station_mac,
    'login_status' => '0'
];
$ch = curl_init("$WIFI_server/set_usage_log");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
$status = curl_exec($ch);
curl_close($ch);*/

$conn->query("INSERT into usage_log (username, mac, status, log_time) VALUES ('$username', '$mac', '0', '$dt_str')");

$sql = "SELECT PLP.type, PLP.profile_name FROM users AS US, profile_login_prop AS PLP WHERE US.username = '$username' AND US.profile = PLP.profile_name";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
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
  <script language="JavaScript">
    function openLogin() {
	if (window.name != 'hotspot_logout') return true;
	open('<?php echo $link_login;?>', '_blank', '');
	window.close();
	return false;
    }
  </script>
</head>

<body>
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="offset-sm-1 offset-md-4 col-sm-10 col-md-4">
                <p class="text-center font-weight-bold">Logged out from MagicShine WiFi</p>
                <table class="table table-bordered">
                    <tbody>
                        <tr><td class="text-center font-weight-bold" style="width:50%;">Package</td><td class="text-center"><?php echo $package; ?></td></tr>
                        <tr><td class="text-center font-weight-bold">Username</td><td class="text-center"><?php echo $username; ?></td></tr>
                        <tr><td class="text-center font-weight-bold">Time Left</td><td class="text-center"><?php echo $time_left; ?></td></tr>
                    </tbody>
                </table>
                <form action="<?php echo $link_login;?>" class="text-center" name="login" onSubmit="return openLogin()">
                    <button type="submit" class="btn btn-info btn-block" value="log in">LOG IN</button>
            </form>
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
