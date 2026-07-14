<?php
session_start();
include('includes/dbconnection.php');

$appointment_id = $_GET['appointment_id'] ?? null;

if($appointment_id) {
    
    mysqli_query($con, "
        UPDATE tblbook 
        SET payment_status='CANCELLED',
            Remark='Payment cancelled by user',
            Status=-1
        WHERE ID='$appointment_id'
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Cancelled</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; }
        .cancel-box { 
            max-width: 500px; 
            margin: 0 auto; 
            padding: 30px; 
            border: 2px solid #f44336; 
            border-radius: 10px; 
        }
        .cancel-icon { font-size: 60px; color: #f44336; }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            margin: 10px; 
            background: #2196F3; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>
    <div class="cancel-box">
        <div class="cancel-icon">✗</div>
        <h1>Payment Cancelled</h1>
        <p>Your appointment booking has been cancelled.</p>
        <p>No charges were made to your account.</p>
        <a href="book-appointment.php" class="btn">Try Again</a>
        <a href="index.php" class="btn">Go to Home</a>
    </div>
</body>
</html>