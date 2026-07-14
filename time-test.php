<?php
date_default_timezone_set('Asia/Manila');
echo "PHP Time: " . date('Y-m-d H:i:s') . "<br>";

include('includes/dbconnection.php');
$result = mysqli_query($con, "SELECT NOW() as mysql_time");
$row = mysqli_fetch_assoc($result);

echo "MySQL Time: " . $row['mysql_time'];
