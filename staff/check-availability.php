<?php
session_start();
include('includes/dbconnection.php');

if (!isset($_SESSION['staff_id']) || $_SESSION['staff_id'] == '') {
    exit('Unauthorized');
}

if (isset($_POST['date'])) {
    $date = mysqli_real_escape_string($con, $_POST['date']);
    $bookedQuery = mysqli_query($con, "
        SELECT AptTime 
        FROM tblbook 
        WHERE AptDate='$date' 
        AND Status IN ('', '1', 'Selected')
        ORDER BY AptTime ASC
    ");
    
    $bookedTimes = [];
    while ($row = mysqli_fetch_array($bookedQuery)) {
        $bookedTimes[] = $row['AptTime'];
    }
    
    $businessStart = strtotime('09:00');
    $businessEnd = strtotime('18:00');
    $interval = 60;
    echo '<div style="margin-top: 20px;">';
    echo '<h5>Booked Time Slots for ' . date('F d, Y', strtotime($date)) . '</h5>';
    if (count($bookedTimes) > 0) {
        echo '<div class="alert alert-info">';
        echo '<strong>Already Booked:</strong><br>';
        foreach ($bookedTimes as $time) {
            echo '<span class="label label-danger" style="margin: 5px; padding: 8px;">' . 
                 date('h:i A', strtotime($time)) . '</span>';
        }
        echo '</div>';
    }
    
    echo '<h5>Available Time Slots</h5>';
    echo '<div>';
    
    $hasAvailable = false;
    for ($time = $businessStart; $time < $businessEnd; $time = strtotime("+$interval minutes", $time)) {
        $timeStr = date('H:i:s', $time);
        
        if (!in_array($timeStr, $bookedTimes)) {
            echo '<span class="label label-success" style="margin: 5px; padding: 8px;">' . 
                 date('h:i A', $time) . '</span>';
            $hasAvailable = true;
        }
    }
    
    if (!$hasAvailable) {
        echo '<div class="alert alert-warning">No available slots for this date.</div>';
    }
    
    echo '</div>';
    echo '</div>';
}
?>