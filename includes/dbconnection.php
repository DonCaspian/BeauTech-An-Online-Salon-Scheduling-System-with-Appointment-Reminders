<?php
date_default_timezone_set('Asia/Manila');
$con = mysqli_connect("localhost", "u272508820_admin", "AuroraAdmin@123", "u272508820_bpmsdb");
if (mysqli_connect_errno()) {
    echo "Connection failed: " . mysqli_connect_error();
    mysqli_query($con, "SET time_zone = '+08:00'");
    exit();
}
?>
