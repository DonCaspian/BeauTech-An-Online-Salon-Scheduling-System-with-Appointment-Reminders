<?php 
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['bpmsuid'])==0) {
    header('location:logout.php');
    exit();
}

if (isset($_GET['cancelid'])) {
    $userid = $_SESSION['bpmsuid'];
    $aptNumber = $_GET['cancelid'];

    $check = mysqli_query($con,"
        SELECT AptDate, AptTime, Status 
        FROM tblbook 
        WHERE AptNumber='$aptNumber' AND UserID='$userid'
    ");

    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);

        $appointmentDateTime = strtotime($row['AptDate'].' '.$row['AptTime']);
        $currentDateTime = time();

        $timeDiff = $appointmentDateTime - $currentDateTime;

        if ($timeDiff >= 86400 && $row['Status'] != 'Cancelled') {
            mysqli_query($con,"
                UPDATE tblbook 
                SET Status='Cancelled' 
                WHERE AptNumber='$aptNumber'
            ");
            echo "<script>alert('Appointment cancelled successfully.');</script>";
        } else {
            echo "<script>alert('Cancellation is only allowed at least 1 day before the appointment.');</script>";
        }
    }
    echo "<script>window.location.href='booking-history.php';</script>";
}
?>
<!doctype html>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=0.46">
<head>
    <title>Beauty Parlour Management System | Booking History</title>
    <link rel="stylesheet" href="assets/css/style-starter.css">
</head>

<body id="home">
<?php include_once('includes/header.php');?>

<section class="w3l-contact-info-main">
<div class="container">
<h4 style="padding-bottom:20px;text-align:center;color:blue;">Appointment History</h4>

<div class="table-responsive">
<table border="2" class="table">
<thead>
<tr>
    <th>#</th>
    <th>Appointment Number</th>
    <th>Appointment Date</th>
    <th>Appointment Time</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php
$userid = $_SESSION['bpmsuid'];
$query = mysqli_query($con,"
    SELECT ID, AptNumber, AptDate, AptTime, Status 
    FROM tblbook 
    WHERE UserID='$userid'
    ORDER BY AptDate DESC
");

$cnt = 1;
if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_array($query)) {

        $appointmentDateTime = strtotime($row['AptDate'].' '.$row['AptTime']);
        $currentDateTime = time();
        $canCancel = ($appointmentDateTime - $currentDateTime) >= 86400 && $row['Status'] != 'Cancelled';
?>
<tr>
    <td><?php echo $cnt; ?></td>
    <td><?php echo $row['AptNumber']; ?></td>
    <td><?php echo $row['AptDate']; ?></td>
    <td><?php echo $row['AptTime']; ?></td>
    <td>
        <?php
        if ($row['Status'] == '') {
            echo "Waiting for confirmation";
        } else {
            echo $row['Status'];
        }
        ?>
    </td>
    <td>
        <a href="appointment-detail.php?aptnumber=<?php echo $row['AptNumber'];?>" class="btn btn-primary btn-sm">
            View
        </a>

        <?php if ($canCancel) { ?>
            <a href="booking-history.php?cancelid=<?php echo $row['AptNumber'];?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Are you sure you want to cancel this appointment?');">
               Cancel
            </a>
        <?php } else { ?>
            <button class="btn btn-secondary btn-sm" disabled>
                Cancel Disabled
            </button>
        <?php } ?>
    </td>
</tr>
<?php 
$cnt++;
} 
} else { ?>
<tr>
    <td colspan="6" style="color:red;text-align:center;">No Record Found</td>
</tr>
<?php } ?>

</tbody>
</table>
</div>
</div>
</section>

<?php include_once('includes/footer.php');?>
</body>
</html>
