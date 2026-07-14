<?php
session_start();

if (!isset($_SESSION['bpmsaid']) || $_SESSION['bpmsaid'] == '') {
    header('Location: index.php');
    exit();
}

session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['bpmsaid'] == 0)) {
  header('location:logout.php');
} else {

  $dateid = isset($_GET['id']) ? $_GET['id'] : null;
  $dateinfo = null;
  if ($dateid) {
    $dateinfo = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM tbldates WHERE ID='$dateid'"));
  }

  if (isset($_GET['month'])) {
    $month = $_GET['month'];
  } else {
    $month = date('Y-m'); 
  }

  $monthStart = date('Y-m-01', strtotime($month));
  $monthEnd   = date('Y-m-t', strtotime($month));
  $monthName  = date('F Y', strtotime($month));
  $prevMonth  = date('Y-m', strtotime($month . " -1 month"));
  $nextMonth  = date('Y-m', strtotime($month . " +1 month"));

  if (isset($_GET['toggledate'])) {
    $id = $_GET['toggledate'];
    $status = $_GET['status'];
    $newstatus = ($status == 1) ? 0 : 1;
    mysqli_query($con, "UPDATE tbldates SET IsAvailable='$newstatus' WHERE ID='$id'");
    echo "<script>window.location.href='manage-timeslots.php?month=$month';</script>";
    exit();
  }

  if (isset($_GET['toggle'])) {
    $slotid = $_GET['toggle'];
    $status = $_GET['status'];
    $newstatus = ($status == 1) ? 0 : 1;
    mysqli_query($con, "UPDATE tbltimeslots SET IsAvailable='$newstatus' WHERE ID='$slotid'");
    if ($dateid) {
      header("location: manage-timeslots.php?id=$dateid");
    } else {
      echo "<script>window.location.href='manage-timeslots.php?month=$month';</script>";
    }
    exit();
  }

  if (isset($_POST['adddate'])) {
    $date = $_POST['date'];
    $exists = mysqli_query($con, "SELECT * FROM tbldates WHERE Date='$date'");
    if (mysqli_num_rows($exists) == 0) {
      mysqli_query($con, "INSERT INTO tbldates (Date, IsAvailable) VALUES ('$date', 1)");
      $msg = "Date added successfully!";
    } else {
      $msg = "Date already exists.";
    }
  }

  if ($dateid && isset($_POST['addslot'])) {
    $time = $_POST['time'];
    $exists = mysqli_query($con, "SELECT * FROM tbltimeslots WHERE SlotTime='$time' AND DateID='$dateid'");
    if (mysqli_num_rows($exists) == 0) {
      mysqli_query($con, "INSERT INTO tbltimeslots (DateID, SlotTime, IsAvailable) VALUES ('$dateid','$time',1)");
      $msg = "Time slot added.";
    } else {
      $msg = "Slot already exists.";
    }
  }
?>
<!DOCTYPE HTML>
<html>
<head>
  <title>BPMS || Manage Dates & Time Slots</title>
  <link href="css/bootstrap.css" rel='stylesheet' type='text/css' />
  <link href="css/style.css" rel='stylesheet' type='text/css' />
  <link href="css/font-awesome.css" rel="stylesheet">
  <link href="css/animate.css" rel="stylesheet" type="text/css" media="all">
  <link href="css/custom.css" rel="stylesheet">
  <link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700' rel='stylesheet' type='text/css'>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap5@6.1.8/index.global.min.js"></script>

  <script src="js/jquery-1.11.1.min.js"></script>
  <script src="js/modernizr.custom.js"></script>
  <script src="js/wow.min.js"></script>
  <script> new WOW().init(); </script>
</head>
<body class="cbp-spmenu-push">
  <div class="main-content">
    <?php include_once('includes/sidebar.php');?>
    <?php include_once('includes/header.php');?>

    <div id="page-wrapper">
      <div class="main-page">

      <?php if (!$dateid) { ?>
      
      <h3 class="title1">Manage Date Availability</h3>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="manage-timeslots.php?month=<?php echo $prevMonth; ?>" class="btn btn-outline-secondary">&laquo; <?php echo date('F', strtotime($prevMonth)); ?></a>
        <h4><?php echo $monthName; ?></h4>
        <a href="manage-timeslots.php?month=<?php echo $nextMonth; ?>" class="btn btn-outline-secondary"><?php echo date('F', strtotime($nextMonth)); ?> &raquo;</a>
      </div>

      <form method="get" class="mb-3">
        <label>Select Month:</label>
        <input type="month" name="month" value="<?php echo $month; ?>" class="form-control d-inline-block" style="width:200px;">
        <button type="submit" class="btn btn-primary">Go</button>
      </form>

      <?php if(isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

      <div class="card shadow p-3 mb-4">
        <form method="post" class="form-inline mb-3">
          <label><strong>Add Date:</strong></label>
          <input type="date" name="date" class="form-control mx-2" required>
          <button type="submit" name="adddate" class="btn btn-success">Add Date</button>
        </form>
        <div id="calendar"></div>
      </div>

      <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          themeSystem: 'bootstrap5',
          initialView: 'dayGridMonth',
          initialDate: '<?php echo $monthStart; ?>',
          headerToolbar: {
            left: 'title',
            center: '',
            right: ''
          },
          events: [
            <?php
              $res = mysqli_query($con, "SELECT * FROM tbldates WHERE Date BETWEEN '$monthStart' AND '$monthEnd' ORDER BY Date ASC");
              while($row = mysqli_fetch_assoc($res)) {
                $color = ($row['IsAvailable'] == 1) ? '#28a745' : '#dc3545';
                echo "{ title: '".($row['IsAvailable'] ? "Available" : "Unavailable")."', start: '".$row['Date']."', color: '".$color."', id: '".$row['ID']."' },";
              }
            ?>
          ],
          eventClick: function(info) {
            var id = info.event.id;
            if(confirm("Open this date to manage its time slots?")) {
              window.location.href = "manage-timeslots.php?id=" + id;
            }
          }
        });
        calendar.render();
      });
      </script>

      <div class="card shadow mt-4">
        <div class="card-body">
          <h4>List of Dates (<?php echo $monthName; ?>)</h4>
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $cnt = 1;
              $res = mysqli_query($con, "SELECT * FROM tbldates WHERE Date BETWEEN '$monthStart' AND '$monthEnd' ORDER BY Date ASC");
              while($row = mysqli_fetch_array($res)) {
              ?>
              <tr>
                <td><?php echo $cnt++; ?></td>
                <td><?php echo date("M d, Y", strtotime($row['Date'])); ?></td>
                <td><?php echo $row['IsAvailable'] ? "<span class='badge bg-success'>Available</span>" : "<span class='badge bg-danger'>Unavailable</span>"; ?></td>
                <td>
                  <a href="manage-timeslots.php?id=<?php echo $row['ID'];?>" class="btn btn-sm btn-primary">Manage Slots</a>
                  <a href="manage-timeslots.php?month=<?php echo $month; ?>&toggledate=<?php echo $row['ID'];?>&status=<?php echo $row['IsAvailable'];?>" class="btn btn-sm btn-warning">Toggle</a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php } else { ?>
      
      <h3 class="title1">Manage Slots for <?php echo date("M d, Y", strtotime($dateinfo['Date'])); ?></h3>
      <?php if(isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

      <form method="post" class="form-inline mb-3">
        <input type="time" name="time" class="form-control mr-2" required>
        <button type="submit" name="addslot" class="btn btn-success">Add Slot</button>
      </form>

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
          while($row = mysqli_fetch_array($ret)){
          ?>
          <tr>
            <td><?php echo $cnt++; ?></td>
            <td><?php echo date("g:i A", strtotime($row['SlotTime'])); ?></td>
            <td><?php echo $row['IsAvailable'] ? "<span class='badge bg-success'>Available</span>" : "<span class='badge bg-danger'>Unavailable</span>"; ?></td>
            <td>
              <a href="manage-timeslots.php?id=<?php echo $dateid;?>&toggle=<?php echo $row['ID'];?>&status=<?php echo $row['IsAvailable'];?>" class="btn btn-sm btn-warning">Toggle</a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>

      <a href="manage-timeslots.php?month=<?php echo date('Y-m', strtotime($dateinfo['Date'])); ?>" class="btn btn-secondary mt-3">← Back to Calendar</a>

      <?php } ?>

      </div>
    </div>

    <?php include_once('includes/footer.php');?>
  </div>

  <script src="js/classie.js"></script>
  <script src="js/jquery.nicescroll.js"></script>
  <script src="js/scripts.js"></script>
  <script src="js/bootstrap.js"></script>
</body>
</html>
<?php } ?>
