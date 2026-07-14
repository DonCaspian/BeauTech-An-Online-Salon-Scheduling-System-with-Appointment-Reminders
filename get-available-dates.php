<?php
include('includes/dbconnection.php');
header('Content-Type: application/json');

$query = "SELECT ID, Date FROM tbldates WHERE IsAvailable = 1";
$result = mysqli_query($con, $query);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = [
        'id' => $row['ID'],
        'title' => 'Available',
        'start' => $row['Date'],
        'color' => '#28a745' 
    ];
}

echo json_encode($events);
?>
