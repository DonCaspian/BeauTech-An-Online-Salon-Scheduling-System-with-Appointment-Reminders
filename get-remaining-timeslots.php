<?php
include('includes/dbconnection.php');

$date = $_GET['date'] ?? '';

$sql = "
SELECT 
    DATE_FORMAT(t.SlotTime, '%h:%i %p') AS time
FROM tbltimeslots t
JOIN tbldates d ON d.ID = t.DateID
WHERE d.Date = '$date'
AND t.IsAvailable = 1
AND NOT EXISTS (
    SELECT 1 FROM tblbook b
    WHERE b.AptDate = d.Date
    AND b.AptTime = t.SlotTime
)
ORDER BY t.SlotTime ASC
";

$res = mysqli_query($con, $sql);

$slots = [];
while ($row = mysqli_fetch_assoc($res)) {
    $slots[] = $row['time'];
}

header('Content-Type: application/json');
echo json_encode($slots);
