<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include('includes/auth.php');

if (strlen($_SESSION['bpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

if (isset($_POST['submit'])) {
    $staffname = mysqli_real_escape_string($con, $_POST['staffname']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    
    $query = mysqli_query($con, "INSERT INTO tblstaff(StaffName, Role, Status) 
                                 VALUES('$staffname', '$role', '$status')");
    
    if ($query) {
        echo "<script>alert('Staff member added successfully');</script>";
        echo "<script>window.location.href='edit-service-staff.php'</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again');</script>";
        echo "<script>window.location.href='edit-service-staff.php'</script>";
    }
}
?>