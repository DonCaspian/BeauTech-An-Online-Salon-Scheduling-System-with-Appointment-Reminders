<?php
include('includes/dbconnection.php');

$date = $_GET['date'] ?? '';
$staffID = $_GET['staffID'] ?? '';

$sql = "
SELECT 
    t.ID,
    DATE_FORMAT(t.SlotTime, '%h:%i %p') AS display_time
FROM tbltimeslots t
JOIN tbldates d ON d.ID = t.DateID
WHERE d.Date = '$date'
AND t.IsAvailable = 1
";

if (!empty($staffID)) {
    $sql .= "
    AND NOT EXISTS (
        SELECT 1 FROM tblbook b
        WHERE b.AptDate = d.Date
        AND b.AptTime = t.SlotTime
        AND b.StaffID = '$staffID'
    )
    ";
}

$sql .= " ORDER BY t.SlotTime ASC";

$res = mysqli_query($con, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = [
        'id'   => $row['ID'],
        'time' => $row['display_time']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);