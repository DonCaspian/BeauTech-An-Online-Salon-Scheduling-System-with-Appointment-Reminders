<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    header('Location: index.php');
    exit();
}

$staffId = $_SESSION['staff_id'];

if (isset($_POST['submit'])) {

    $cid    = $_GET['viewid'];
    $remark = $_POST['remark'];
    $status = $_POST['status'];
    $date   = date('Y-m-d H:i:s');

    $query = mysqli_query($con, "
        UPDATE tblbook 
        SET 
            Remark='$remark',
            Status='$status',
            RemarkDate='$date',
            AcceptedBy='$staffId',
            AcceptedRole='staff',
            AcceptedDate='$date'
        WHERE ID='$cid'
    ");

    if ($query) {
        echo "<script>alert('All remark has been updated.');</script>";
        echo "<script>window.location.href='all-appointment.php';</script>";
    } else {
        echo "<script>alert('Something Went Wrong. Please try again');</script>";
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>BPMS || View Appointment</title>

<link href="css/bootstrap.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/font-awesome.css" rel="stylesheet">
<link href="css/animate.css" rel="stylesheet">
<link href="css/custom.css" rel="stylesheet">

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/wow.min.js"></script>
<script>new WOW().init();</script>
<script src="js/metisMenu.min.js"></script>
<script src="js/custom.js"></script>
</head>

<body class="cbp-spmenu-push">
<div class="main-content">

<?php include_once('includes/sidebar.php'); ?>
<?php include_once('includes/header.php'); ?>

<div id="page-wrapper">
<div class="main-page">
<div class="tables">

<h3 class="title1">View Appointment</h3>
<div class="table-responsive bs-example widget-shadow">

<h4>View Appointment:</h4>

<?php
$cid = $_GET['viewid'];

$ret = mysqli_query($con, "
    SELECT 
        tbluser.FirstName,
        tbluser.LastName,
        tbluser.Email,
        tbluser.MobileNumber,
        tblbook.AptNumber,
        tblbook.AptDate,
        tblbook.AptTime,
        tblbook.Message,
        tblbook.BookingDate,
        tblbook.Remark,
        tblbook.Status,
        tblbook.RemarkDate,
        tblbook.AcceptedBy,
        tblbook.AcceptedRole,
        tblbook.AcceptedDate,
        staff.StaffName
    FROM tblbook
    JOIN tbluser ON tbluser.ID = tblbook.UserID
    LEFT JOIN tblAdStaff AS staff 
        ON staff.ID = tblbook.AcceptedBy
        AND tblbook.AcceptedRole = 'staff'
    WHERE tblbook.ID='$cid'
");

$row = mysqli_fetch_array($ret);
?>

<table class="table table-bordered">
<tr><th>Appointment Number</th><td><?php echo $row['AptNumber']; ?></td></tr>
<tr><th>Name</th><td><?php echo $row['FirstName'].' '.$row['LastName']; ?></td></tr>
<tr><th>Email</th><td><?php echo $row['Email']; ?></td></tr>
<tr><th>Mobile Number</th><td><?php echo $row['MobileNumber']; ?></td></tr>
<tr><th>Appointment Date</th><td><?php echo $row['AptDate']; ?></td></tr>
<tr><th>Appointment Time</th><td><?php echo $row['AptTime']; ?></td></tr>
<tr><th>Apply Date</th><td><?php echo $row['BookingDate']; ?></td></tr>

<tr>
<th>Status</th>
<td><?php echo empty($row['Status']) ? 'Not Updated Yet' : $row['Status']; ?></td>
</tr>

<tr>
<th>Approved / Rejected By</th>
<td>
<?php
if ($row['AcceptedRole'] === 'staff') {
    echo htmlspecialchars($row['StaffName']) .
         "<br><small class='text-muted'>" .
         date('M d, Y h:i A', strtotime($row['AcceptedDate'])) .
         "</small>";
} else {
    echo "Pending";
}
?>
</td>
</tr>
</table>

<?php if (empty($row['Status'])) { ?>

<form method="post">
<table class="table table-bordered">
<tr>
<th>Remark</th>
<td><textarea name="remark" class="form-control" rows="5" required></textarea></td>
</tr>

<tr>
<th>Status</th>
<td>
<select name="status" class="form-control" required>
<option value="">Select</option>
<option value="Selected">Approved</option>
<option value="Rejected">Rejected</option>
</select>
</td>
</tr>

<tr>
<td colspan="2" align="center">
<button type="submit" name="submit" class="btn btn-primary">Submit</button>
</td>
</tr>
</table>
</form>

<?php } else { ?>

<table class="table table-bordered">
<tr><th>Remark</th><td><?php echo $row['Remark']; ?></td></tr>
<tr><th>Type of Service</th><td><?php echo $row['Message']; ?></td></tr>
<tr><th>Remark Date</th><td><?php echo $row['RemarkDate']; ?></td></tr>
</table>

<?php } ?>

</div>
</div>
</div>
</div>

<?php include_once('includes/footer.php'); ?>

</div>

<script src="js/classie.js"></script>
<script src="js/jquery.nicescroll.js"></script>
<script src="js/scripts.js"></script>
<script src="js/bootstrap.js"></script>

</body>
</html>