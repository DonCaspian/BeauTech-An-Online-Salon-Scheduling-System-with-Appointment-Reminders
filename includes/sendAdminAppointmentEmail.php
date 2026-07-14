<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

function sendAdminAppointmentEmail($appointmentData) {

    $mail = new PHPMailer(true);

    try {
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@aurorasbymimie.online';
        $mail->Password   = 'AuroraPW@123'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('noreply@aurorasbymimie.online', 'BeautyParlour');
        $mail->addAddress('estiebaluyot2@gmail.com', 'Admin'); 

        $mail->isHTML(true);
        $mail->Subject = '📅 New Appointment Created';

        $mail->Body = "
            <h3>New Appointment Notification</h3>
            <p><strong>Customer:</strong> {$appointmentData['name']}</p>
            <p><strong>Service:</strong> {$appointmentData['service']}</p>
            <p><strong>Date:</strong> {$appointmentData['date']}</p>
            <p><strong>Time:</strong> {$appointmentData['time']}</p>
            <p>Please log in to the admin panel for more details.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Admin email error: ' . $mail->ErrorInfo);
        return false;
    }
}
