<?php
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . '/../includes/dbconnection.php');
require(__DIR__ . '/../includes/sendReminderEmail.php');

if (!isset($con)) {
    die('Database connection not loaded.');
}

$query = mysqli_query($con, "
    SELECT 
        b.ID,
        b.AptDate,
        b.AptTime,
        b.Service,
        u.FirstName,
        u.LastName,
        u.Email
    FROM tblbook b
    JOIN tbluser u ON u.ID = b.UserID
    WHERE 
        b.AptDate = CURDATE()
        AND b.ReminderSent = 0
");

while ($row = mysqli_fetch_assoc($query)) {

    $name = $row['FirstName'] . ' ' . $row['LastName'];

    $result = sendReminder(
        $row['Email'],
        $name,
        $row['AptDate'],
        date('g:i A', strtotime($row['AptTime'])),
        $row['Service']
    );

    if ($result === true) {
        mysqli_query($con, "UPDATE tblbook SET ReminderSent=1 WHERE ID='{$row['ID']}'");
    } else {
        echo "Email failed: " . $result;
    }
}
