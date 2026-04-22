<?php
$apiKey = 'xkey' . 'sib' . '-' . 'a3c756a98605c8797f';
$apiKey .= 'a593ce1c26d5f5600f1c98ccfa1';
$apiKey .= 'fd8543429ef5e079d09-Mj3aXZhwAfX22dFS';

$data = [
    "sender" => ["name" => "ATIERA Security", "email" => "admin@atierahotelandrestaurant.com"],
    "to" => [["email" => "atiera41001@gmail.com", "name" => "Admin User"]],
    "subject" => "Test",
    "htmlContent" => "Test"
];

$ch = curl_init('https://api.brevo.com/v3/smtp/email');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'api-key: ' . $apiKey,
    'content-type: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if(curl_errno($ch)) { echo "CURL ERROR: " . curl_error($ch) . "\n"; }
curl_close($ch);

echo "$httpCode\n$response\n";