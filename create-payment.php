<?php
include('includes/dbconnection.php');
include('includes/paymongo.php');

$appointment_id = $_POST['appointment_id'];
$amount = $_POST['amount'] * 100; 

$data = [
  "data" => [
    "attributes" => [
      "line_items" => [[
        "name" => "Salon Appointment",
        "amount" => $amount,
        "currency" => "PHP",
        "quantity" => 1
      ]],
      "payment_method_types" => ["gcash", "card", "paymaya"],
      "success_url" => "https://yourdomain.com/payment-success.php",
      "cancel_url" => "https://yourdomain.com/payment-cancel.php",
      "description" => "Appointment #$appointment_id"
    ]
  ]
];

$ch = curl_init(PAYMONGO_API_URL . "/checkout_sessions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ":"),
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$checkout_id = $response['data']['id'];
$checkout_url = $response['data']['attributes']['checkout_url'];

mysqli_query($con, "
  UPDATE tblappointment 
  SET paymongo_checkout_id='$checkout_id' 
  WHERE ID='$appointment_id'
");

header("Location: $checkout_url");
exit;
