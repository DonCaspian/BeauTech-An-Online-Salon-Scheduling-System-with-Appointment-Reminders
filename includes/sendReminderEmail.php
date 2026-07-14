<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

function sendReminder($email, $name, $date, $time, $service) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@aurorasbymimie.online';
        $mail->Password   = 'AuroraPW@123';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('noreply@aurorasbymimie.online', 'Beauty Parlour');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Appointment Reminder';
        $mail->Body    = "
            <h3>Appointment Reminder</h3>
            <p>Hello <b>$name</b>,</p>
            <p>This is a reminder for your appointment:</p>
            <ul>
              <li><b>Service:</b> $service</li>
              <li><b>Date:</b> $date</li>
              <li><b>Time:</b> $time</li>
            </ul>
            <p>We look forward to seeing you!</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
