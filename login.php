<?php
include "lib/connect_db.php";
require('lib/routeros_api.class.php');
include("configs.php");

$mac = trim($_GET["mac"]);

//Get mac from cookies
if(isset($_COOKIE["c_mac"])) {
    $mac = trim($_COOKIE["c_mac"]);
}

$error = trim($_GET["error"]);
if($error == "invalid password")
{
    $sql = "DELETE FROM user_mac_status WHERE mac = '$mac'";
    $result = $conn->query($sql);
    header("Location: $MKGateway");
    exit();
}
$chap_id = $_GET["chap_id"];
$chap_challenge = $_GET["chap_challenge"];

if(!$chap_id || $mac =="")
{
    echo "Illegal response. An error has occured.";
    exit();
}

$user = "";
$pass = "";

if($error)
    goto Problem;

$sql = "SELECT username, update_count FROM user_mac_status WHERE mac = '$mac' ORDER BY update_time DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0)
{
    $row = $result->fetch_assoc();
    $user = $row["username"];
    $update_count = $row["update_count"];
    $sql = "SELECT PLP.type, US.profile, US.password FROM users AS US, profile_login_prop AS PLP WHERE US.username = '$user' AND US.profile = PLP.profile_name";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if($row["type"] == "LS")
    {
        $pass = $row["password"];
        $profile = $row["profile"];
        //gae

            $post = [
                'mac' => $mac
            ];
            $ch = curl_init("$WIFI_server/get_user_status");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $status = curl_exec($ch);
            curl_close($ch);

            $s_array = explode(";",$status);

            if($s_array[0] == "E")
            {
                //DELETE FROM RPI
                $sql = "DELETE FROM users WHERE username = '$user'";
                $result = $conn->query($sql);
                $sql = "DELETE FROM user_mac_status WHERE mac = '$mac'";
                $result = $conn->query($sql);
                $sql = "DELETE FROM user_mac_status WHERE username = '".$s_array[1]."'";
                $result = $conn->query($sql);
                header("Location: $MKGateway");
                exit();

                //delete from mikrotik
                $API = new RouterosAPI();
                if($API->connect($MKTWanIP, 'admin', 'enigma') )
                {
                    $ARRAY = $API->comm('/tool/user-manager/user/print', array(".proplist" => ".id", "?username" => $user));
                    $response = $API->comm("/tool/user-manager/user/remove",array(".id" => $ARRAY[0]['.id']));
                }
                $API->disconnect();

                $pass = "";
            }
            else if($s_array[0] == "Y")
            {
                WriteUserToMikroTik(new RouterosAPI(), $MKTWanIP, "admin", "enigma", "admin", $user, $pass, $profile);
            }
            else
            {
                $pass = "";
            }


    }
}
else
{
    $post = [
        'mac' => $mac
    ];
    $ch = curl_init("$WIFI_server/get_user_status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $status = curl_exec($ch);
    curl_close($ch);

    $s_array = explode(";",$status);

    if($s_array[0] == "Y")
    {
        $user = trim($s_array[1]);
        $pass = trim($s_array[2]);
        $profile = trim($s_array[3]);

        if($user > 9999 && $pass != "" && $profile != "")
        {
            WriteUserToMikroTik(new RouterosAPI(), $MKTWanIP, "admin", "enigma", "admin", $user, $pass, $profile);
            $sql = "INSERT into users (username, password, profile) VALUES ('$user', '$pass', '$profile')  ";
            $result = $conn->query($sql);
        }
        else
        {
            $user = "";
            $pass = "";
        }
    }
    else if($s_array[0] == "E")
    {
        //delete from mikrotik
        $user = trim($s_array[1]);
        $API = new RouterosAPI();
        if($API->connect($MKTWanIP, 'admin', 'enigma') )
        {
            $ARRAY = $API->comm('/tool/user-manager/user/print', array(".proplist" => ".id", "?username" => $user));
            $response = $API->comm("/tool/user-manager/user/remove",array(".id" => $ARRAY[0]['.id']));
        }
        $API->disconnect();

        $pass = "";
    }
}

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

Problem:

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Wifi Login</title>
  <!-- Font Awesome -->
  <link href="font_awesome/css/all.css" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="css/mdb.min.css" rel="stylesheet">
  <!-- Your custom styles (optional) -->
  <link href="css/style.css" rel="stylesheet">
</head>

<body>
<?php if($chap_id){?>
	<form name="sendin" action="<?php echo $MKGateway;?>/login" method="post">
		<input type="hidden" name="username" />
		<input type="hidden" name="password" />
		<input type="hidden" name="dst" value="<?php echo $link_orig; ?>" />
		<input type="hidden" name="popup" value="false" />
	</form>

	<script type="text/javascript" src="lib/md5.js"></script>
	<script type="text/javascript">
            function doAutoLogin()
            {
                document.sendin.username.value = "<?php echo $user;?>";
                document.sendin.password.value = hexMD5('<?php echo $chap_id;?>' + "<?php echo $pass;?>" + '<?php echo $chap_challenge;?>');
                document.sendin.submit();
            }
	    function doLogin() {

                  // Fire off the request to /form.php
                    var request = $.ajax({
                        url: "validate_user.php",
                        type: "post",
                        data: {"username": document.login.username.value, "password": document.login.password.value, "mac":document.login.mac.value}
                    });

                    // Callback handler that will be called on success
                    request.done(function (response, textStatus, jqXHR){
                            if(response == "T")
                            {
                                document.sendin.username.value = document.login.username.value;
                                document.sendin.password.value = hexMD5('<?php echo $chap_id;?>' + document.login.password.value + '<?php echo $chap_challenge;?>');
                                document.sendin.submit();
                            }
                            else if(response == "P")
                            {
                                ShowAlert("Error, invalid profile.");
                                return;
                            }
                            else if(response == "FL")
                            {
                                ShowAlert("Max user limit reached. mac=<?php echo $mac;?>");
                                return;
                            }
                            else if(response == "N")
                            {
                                ShowAlert("Invalid Username and Password.");
                                return;
                            }
                            else if(response == "E")
                            {
                                ShowAlert("Username expired.");
                                return;
                            }
                            else
                            {
                                ShowAlert("Unknown error.");
                                return;
                            }

                    });


                    request.fail(function (jqXHR, textStatus, errorThrown){
                            ShowAlert("Error connecting to the server.");
                    });

	    }
	</script>
<?php }?>
<div class="container">
    <div class="row mt-5">
        <div class="offset-sm-1 offset-md-4 col-sm-10 col-md-4">
        <!-- Default form subscription -->
        <form class="text-center border border-light p-5" name="login" action="<?php echo $MKGateway;?>/login" method="post" <?php if($chap_id) {?> onSubmit="return doLogin()" <?php }?>>
            <input type="hidden" name="dst" value="<?php echo $link_orig; ?>" />
	    <input type="hidden" name="popup" value="false" />
            <input type="hidden" name="mac" value="<?php echo $mac;?>"/>
            <p class="h4 mb-4"><i class="fa fa-wifi"></i> MagicShine WiFi</p>
            <div id="alert_div">
                <?php if($error) {?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
            </div>

            <input type="text" name="username" class="form-control mb-4" value="<?php echo $_COOKIE["user"];?>" placeholder="Username">
            <input type="password" name="password" class="form-control mb-4" value="<?php echo $_COOKIE["pass"];?>" placeholder="Password">
            <button class="btn btn-info btn-block" type="button" onclick="doLogin();">Login</button>
        </form>
<!-- Default form subscription -->
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
<script type="text/javascript">
  $(document).ready(function () {
      document.login.username.focus();
      <?php if($user != "" && $pass != ""){?>
              doAutoLogin();
      <?php }?>
  });

  function ShowAlert(err_message)
  {
      $("#alert_div").html("<div class='alert alert-danger'>"+err_message+"</div>");
  }
</script>
</body>

</html>
