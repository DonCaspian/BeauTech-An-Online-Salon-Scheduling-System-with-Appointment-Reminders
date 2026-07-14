<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include('includes/auth.php');

if (strlen($_SESSION['bpmsaid']==0)) {
    header('location:logout.php');
    exit();
}

if (isset($_POST['submit'])) {

    $service = $_POST['service'];
    $date    = $_POST['date'];
    $time    = $_POST['time'];

    $aptno = mt_rand(100000000, 999999999);

    $query = mysqli_query($con, "
        INSERT INTO tblbook
        (UserID, AptNumber, AptDate, AptTime, Service, Message, BookingDate, Status, ReminderSent)
        VALUES
        (
            0,
            '$aptno',
            '$date',
            '$time',
            '$service',
            'Walk-in appointment',
            NOW(),
            'Selected',
            0
        )
    ");

    if ($query) {
        echo "<script>alert('Walk-in appointment added successfully');</script>";
        echo "<script>window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Something went wrong');</script>";
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
<title>BPMS | Walk-In Appointment</title>

<meta name="viewport" content="width=device-width, initial-scale=0.40">

<link href="css/bootstrap.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/font-awesome.css" rel="stylesheet">
<link href="css/custom.css" rel="stylesheet">

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/bootstrap.js"></script>
</head>

<body class="cbp-spmenu-push">
<div class="main-content">

    <?php include_once('includes/sidebar.php'); ?>
    <?php include_once('includes/header.php'); ?>

    <div id="page-wrapper">
        <div class="main-page">

            <h3 class="title1">Add Walk-In Appointment</h3>

            <div class="row">
                <div class="col-md-8">

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Walk-In Appointment Details
                        </div>

                        <div class="panel-body">
                            <form method="post">

                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>Service</label>
                                    <select name="service" class="form-control" required>
                                        <option value="">Select Service</option>
                                        <?php
                                        $services = mysqli_query($con, "SELECT * FROM tblservices");
                                        while ($row = mysqli_fetch_array($services)) {
                                            echo "<option value='{$row['ID']}'>{$row['ServiceName']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Staff</label>
                                    <select name="staff" class="form-control" required>
                                        <option value="">Select Staff</option>
                                        <?php
                                        $staff = mysqli_query($con, "SELECT * FROM tblstaff");
                                        while ($row = mysqli_fetch_array($staff)) {
                                            echo "<option value='{$row['ID']}'>{$row['FullName']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Appointment Date</label>
                                    <input type="date" name="date" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>Appointment Time</label>
                                    <input type="time" name="time" class="form-control" required>
                                </div>

                                <button type="submit" name="submit" class="btn btn-success">
                                    Save Walk-In Appointment
                                </button>

                                <a href="dashboard.php" class="btn btn-default">
                                    Cancel
                                </a>

                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</div>
</body>
</html>
