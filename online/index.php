<?php
include "../lib/connect_db.php";

$sql = "SELECT * FROM usage_log ORDER BY log_time DESC";
$result = $conn->query($sql);

$num_row = 0;
while ($row = $result->fetch_assoc()) {
    $num_row++;
    $tb_content .= "<tr>";
    $tb_content .= "<td>$num_row.</td>";
    $tb_content .= "<td>$row[username]</td>";
    $tb_content .= "<td>$row[mac]</td>";
    if($row["status"] == 1)
        $tb_content .= "<td><span class='badge badge-primary'>Log in</span></td>";
    else if($row["status"] == 0)
        $tb_content .= "<td><span class='badge badge-danger'>Log out</span></td>";
    $tb_content .= "<td>$row[log_time]</td>";
    $tb_content .= "</tr>";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Wifi Login</title>
  <!-- Font Awesome -->
  <link href="../font_awesome/css/all.css" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="../css/mdb.min.css" rel="stylesheet">
  <!-- MDBootstrap Datatables  -->
  <link href="../css/addons/datatables.min.css" rel="stylesheet">
  <!-- Your custom styles (optional) -->
  <link href="../css/style.css" rel="stylesheet">
</head>

<body>
<div class="container">

    <div class="row">
        
        <div class="col-md-10 offset-md-1 jumbotron">
                <div class="row">
        <div class="col-md-12" style="text-align: center;">
            <h1>Usage Log</h1>
        </div>
    </div>
		<div class="row">
			<div class="col-md-12">
				<span><strong>Total <?php echo $num_row;?> Logs</strong></span>
				<hr class="mb-3">
			</div>
		</div>
<!-- data table -->
		<div class="row">
			<div class="col-md-12 table-responsive">
<table id="m_table" class="table table-sm" cellspacing="0" width="100%">
  <thead>
    <tr>
      <th class="th-sm">No.</th>
      <th class="th-sm">Username</th>
      <th class="th-sm">Mac Address</th>
      <th class="th-sm">Status</th>
      <th class="th-sm">Time</th>
    </tr>
  </thead>
  <tbody>
    <?php echo $tb_content;?>
  </tbody>
  <tfoot>
    <tr>
      <th class="th-sm">No.</th>
      <th class="th-sm">Username</th>
      <th class="th-sm">Mac Address</th>
      <th class="th-sm">Status</th>
      <th class="th-sm">Time</th>
    </tr>
  </tfoot>
</table>
<!-- data table -->
</div>
</div>
</div>
    </div>
</div>
<script type="text/javascript" src="../js/jquery-3.3.1.min.js"></script>
  <!-- Bootstrap tooltips -->
  <script type="text/javascript" src="../js/popper.min.js"></script>
  <!-- Bootstrap core JavaScript -->
  <script type="text/javascript" src="../js/bootstrap.min.js"></script>
  <!-- MDB core JavaScript -->
  <script type="text/javascript" src="../js/mdb.js"></script>
  <!-- MDBootstrap Datatables  -->
  <script type="text/javascript" src="../js/addons/datatables.min.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
      $('#m_table').DataTable();
  });
</script>
</body>

</html>
