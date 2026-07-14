<?php
include('../includes/dbconnection.php');
include('../includes/paymongo.php');

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

$expected = hash_hmac(
  'sha256',
  $payload,
  'whsec_xxxxxxxxxxxxx'
);

if (!hash_equals($expected, $signature)) {
  http_response_code(401);
  exit('Invalid signature');
}

$data = json_decode($payload, true);
$event = $data['data']['attributes']['type'];

if ($event === 'checkout.session.completed') {
    $checkout_id = $data['data']['attributes']['data']['id'];
    $payment_id = $data['data']['attributes']['data']['attributes']['payment_intent_id'];

    mysqli_query($con, "
      UPDATE tblappointment 
      SET payment_status='PAID', paymongo_payment_id='$payment_id'
      WHERE paymongo_checkout_id='$checkout_id'
    ");
}

http_response_code(200);
