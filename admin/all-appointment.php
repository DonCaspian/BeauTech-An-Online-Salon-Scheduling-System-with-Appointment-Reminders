<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['bpmsaid']) || $_SESSION['bpmsaid'] == '') {
    header('Location: index.php');
    exit();
}

if (isset($_GET['delid'])) {
    $sid = $_GET['delid'];
    
    $check = mysqli_query($con, "SELECT AcceptedBy FROM tblbook WHERE ID='$sid'");
    $rowCheck = mysqli_fetch_array($check);

    if (empty($rowCheck['AcceptedBy'])) {
        mysqli_query($con, "DELETE FROM tblbook WHERE ID='$sid'");
        echo "<script>alert('Appointment deleted successfully');</script>";
    } else {
        echo "<script>alert('Cannot delete an accepted appointment');</script>";
    }

    echo "<script>window.location.href='all-appointment.php';</script>";
}
?>

<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=0.45">
<title>BPMS || All Appointment</title>

<link href="css/bootstrap.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/font-awesome.css" rel="stylesheet">
<link href="css/animate.css" rel="stylesheet">
<link href="css/custom.css" rel="stylesheet">

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/modernizr.custom.js"></script>
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

<h3 class="title1">All Appointment</h3>

<div class="table-responsive bs-example widget-shadow">
<h4>All Appointment:</h4>

<table class="table table-bordered">
<thead>
<tr>
    <th>#</th>
    <th>Appointment Number</th>
    <th>Name</th>
    <th>Mobile Number</th>
    <th>Appointment Date</th>
    <th>Appointment Time</th>
    <th>Status</th>
    <th>Accepted By</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php
$ret = mysqli_query($con, "
    SELECT 
    tbluser.FirstName,
    tbluser.LastName,
    tbluser.MobileNumber,
    tblbook.ID AS bid,
    tblbook.AptNumber,
    tblbook.AptDate,
    tblbook.AptTime,
    tblbook.Status,
    tblbook.AcceptedBy,
    tblbook.AcceptedDate,
    tblbook.AcceptedRole, 
    tbladmin.AdminName,
    tblAdStaff.StaffName  
FROM tblbook
JOIN tbluser ON tbluser.ID = tblbook.UserID
LEFT JOIN tbladmin ON tbladmin.ID = tblbook.AcceptedBy AND tblbook.AcceptedRole = 'admin'
LEFT JOIN tblAdStaff ON tblAdStaff.ID = tblbook.AcceptedBy AND tblbook.AcceptedRole = 'staff'
");

$cnt = 1;
while ($row = mysqli_fetch_array($ret)) {
?>

<tr>
    <th scope="row"><?php echo $cnt; ?></th>
    <td><?php echo $row['AptNumber']; ?></td>
    <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
    <td><?php echo $row['MobileNumber']; ?></td>
    <td><?php echo $row['AptDate']; ?></td>
    <td><?php echo $row['AptTime']; ?></td>

    <td>
        <?php
        if (empty($row['Status'])) {
            echo "<span class='text-warning'>Not Updated Yet</span>";
        } else {
            echo "<span class='text-success'>" . $row['Status'] . "</span>";
        }
        ?>
    </td>

    <td>
        <?php
        if (!empty($row['AcceptedBy'])) {
            if ($row['AcceptedRole'] == 'admin') {
                echo $row['AdminName'] . " <span class='badge badge-primary' style='font-size:10px;'></span>";
            } elseif ($row['AcceptedRole'] == 'staff') {
                echo $row['StaffName'] . " <span class='badge badge-info' style='font-size:10px;'></span>";
            }
    
            if (!empty($row['AcceptedDate'])) {
                echo "<br><small class='text-muted'>" .
                     date('M d, Y h:i A', strtotime($row['AcceptedDate'])) .
                     "</small>";
            }
        } else {
            echo "<span class='text-warning'>Pending</span>";
        }
        ?>
    </td>

    <td width="180">
        <a href="view-appointment.php?viewid=<?php echo $row['bid']; ?>" 
           class="btn btn-primary btn-sm">View</a>

        <?php if (empty($row['AcceptedBy'])) { ?>
            <a href="all-appointment.php?delid=<?php echo $row['bid']; ?>" 
               class="btn btn-danger btn-sm"
               onclick="return confirm('Are you sure you want to delete?');">
               Delete
            </a>
        <?php } ?>
    </td>
</tr>

<?php
$cnt++;
}
?>

</tbody>
</table>
</div>
</div>
</div>
</div>

<?php include_once('includes/footer.php'); ?>

<script src="js/classie.js"></script>
<script src="js/jquery.nicescroll.js"></script>
<script src="js/scripts.js"></script>
<script src="js/bootstrap.js"></script>

</div>
</body>
</html>
