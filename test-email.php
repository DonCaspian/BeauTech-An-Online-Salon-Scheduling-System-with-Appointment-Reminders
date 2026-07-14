<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'includes/sendReminderEmail.php';

$result = sendReminder(
    'caspianbaluyot02@gmail.com',
    'Test User',
    date('Y-m-d'),
    '10:00 AM',
    'Test Service'
);

if ($result === true) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Email failed: <pre>$result</pre>";
}
