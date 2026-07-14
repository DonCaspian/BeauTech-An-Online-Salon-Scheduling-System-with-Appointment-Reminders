<?php
session_start();
include('includes/dbconnection.php');
include('includes/paymongo.php');

$appointment_id = $_GET['appointment_id'] ?? null;

if($appointment_id) {
    
    $query = mysqli_query($con, "
        SELECT * FROM tblbook 
        WHERE ID='$appointment_id'
    ");
    $appointment = mysqli_fetch_array($query);
    
    if($appointment) {
        $checkout_id = $appointment['paymongo_checkout_id'];
        
        $ch = curl_init(PAYMONGO_API_URL . "/checkout_sessions/$checkout_id");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ":")
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $sessionData = json_decode($response, true);
        
        if(isset($sessionData['data']) && $sessionData['data']['attributes']['status'] == 'paid') {
            $payment_intent_id = $sessionData['data']['attributes']['payment_intent']['id'] ?? null;
            $payment_method = $sessionData['data']['attributes']['payment_method_used'] ?? 'unknown';
            
            mysqli_query($con, "
                UPDATE tblbook
                SET payment_status='PAID',
                    paymongo_payment_intent_id='$payment_intent_id',
                    payment_method='$payment_method',
                    paid_at=NOW(),
                    Status=1
                WHERE ID='$appointment_id'
            ");
            
            $message = "Payment successful! Your appointment is confirmed.";
            $apt_number = $appointment['AptNumber'];
        } else {
            $message = "Payment verification pending. Please contact us if you've been charged.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; }
        .success-box { 
            max-width: 500px; 
            margin: 0 auto; 
            padding: 30px; 
            border: 2px solid #4CAF50; 
            border-radius: 10px; 
        }
        .success-icon { font-size: 60px; color: #4CAF50; }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            margin: 10px; 
            background: #4CAF50; 
            color: white;
            text-decoration: none; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <p><?php echo $message; ?></p>
        <?php if(isset($apt_number)): ?>
            <p><strong>Appointment Number:</strong> <?php echo $apt_number; ?></p>
        <?php endif; ?>
        <a href="index.php" class="btn">Go to Home</a>
        <a href="booking-history.php" class="btn">View My Bookings</a>
    </div>
</body>
</html>