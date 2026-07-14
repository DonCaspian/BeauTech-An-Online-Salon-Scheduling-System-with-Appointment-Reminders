<?php
session_start();

if (!isset($_SESSION['bpmsaid']) || $_SESSION['bpmsaid'] == '') {
    header('Location: index.php');
    exit();
}

session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['bpmsaid']==0)) {
  header('location:logout.php');
} else {

  $dateid = $_GET['id'];
  $dateinfo = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM tbldates WHERE ID='$dateid'"));

  if (isset($_GET['toggle'])) {
    $slotid = $_GET['toggle'];
    $status = $_GET['status'];
    $newstatus = ($status == 1) ? 0 : 1;
    mysqli_query($con, "UPDATE tbltimeslots SET IsAvailable='$newstatus' WHERE ID='$slotid'");
    header("location: manage-date.php?id=$dateid");
  }

  if (isset($_POST['addslot'])) {
    $time = $_POST['time'];
    $exists = mysqli_query($con, "SELECT * FROM tbltimeslots WHERE SlotTime='$time' AND DateID='$dateid'");
    if (mysqli_num_rows($exists) == 0) {
      mysqli_query($con, "INSERT INTO tbltimeslots (DateID, SlotTime, IsAvailable) VALUES ('$dateid', '$time', 1)");
      $msg = "Time slot added successfully!";
    } else {
      $msg = "Slot already exists.";
    }
  }
?>
<!DOCTYPE HTML>
<html>
<head>
  <title>BPMS | Manage Time Slots</title>

  <link href="admin/css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="admin/css/style.css" rel="stylesheet" type="text/css" />
  <link href="admin/css/font-awesome.css" rel="stylesheet">
  <link href="admin/css/animate.css" rel="stylesheet" type="text/css" media="all">
  <link href="admin/css/custom.css" rel="stylesheet">
  <link href="//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,300italic,400italic,700,700italic" rel="stylesheet" type="text/css">

  <script src="../js/jquery-1.11.1.min.js"></script>
  <script src="../js/modernizr.custom.js"></script>
  <script src="../js/wow.min.js"></script>
  <script src="../js/metisMenu.min.js"></script>
  <script src="../js/custom.js"></script>
  <script>
    new WOW().init();
  </script>
</head>
<body class="cbp-spmenu-push">
  <div class="main-content">

    <?php include_once('includes/sidebar.php');?>

    <?php include_once('includes/header.php');?>

    <div id="page-wrapper">
      <div class="main-page">
        <div class="tables">
          <h3 class="title1">
            Manage Slots for <?php echo date("M d, Y", strtotime($dateinfo['Date'])); ?>
          </h3>

          <?php if(isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

          <div class="table-responsive bs-example widget-shadow">
            <h4>Add New Slot:</h4>
            <form method="post" class="form-inline mb-3">
              <input type="time" name="time" class="form-control mr-2" required>
              <button type="submit" name="addslot" class="btn btn-success">Add Slot</button>
            </form>
          </div>

          <div class="table-responsive bs-example widget-shadow mt-4">
            <h4>Existing Time Slots:</h4>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Time Slot</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $ret = mysqli_query($con, "SELECT * FROM tbltimeslots WHERE DateID='$dateid' ORDER BY SlotTime ASC");
                $cnt = 1;
                while ($row = mysqli_fetch_array($ret)) {
                ?>
                <tr>
                  <td><?php echo $cnt++; ?></td>
                  <td><?php echo date("g:i A", strtotime($row['SlotTime'])); ?></td>
                  <td>
                    <?php 
                      if ($row['IsAvailable'] == 1)
                        echo "<span class='badge badge-success'>Available</span>";
                      else
                        echo "<span class='badge badge-danger'>Unavailable</span>";
                    ?>
                  </td>
                  <td>
                    <a href="manage-date.php?id=<?php echo $dateid;?>&toggle=<?php echo $row['ID'];?>&status=<?php echo $row['IsAvailable'];?>" class="btn btn-sm btn-warning">Toggle</a>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>

            <a href="manage-timeslots.php" class="btn btn-secondary mt-3">← Back to Dates</a>
          </div>
        </div>
      </div>
    </div>

    <?php include_once('includes/footer.php');?>
  </div>

  <script src="../js/classie.js"></script>
  <script src="../js/jquery.nicescroll.js"></script>
  <script src="../js/scripts.js"></script>
  <script src="../js/bootstrap.js"></script>
</body>
</html>
<?php } ?>
