<?php
include('includes/dbconnection.php');

if (!isset($_GET['category'])) {
    echo json_encode([]);
    exit;
}

$category = mysqli_real_escape_string($con, $_GET['category']);

$query = mysqli_query($con, "
    SELECT ID, StaffName
    FROM tblstaff
    WHERE Role='$category' AND Status='Active'
    ORDER BY StaffName ASC
");

$staff = [];
while ($row = mysqli_fetch_assoc($query)) {
    $staff[] = [
        'id' => $row['ID'],
        'name' => $row['StaffName']
    ];
}

echo json_encode($staff);
