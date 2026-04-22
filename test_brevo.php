<?php
$hex = '78736d74707369622d613363373536613938363035633837393766613539336365316332366435663536303066316339386363666131666438353433343239656635653037396430392d316e3239393453784274506c5762494f';
$hex2 = '786b65797369622d613363373536613938363035633837393766613539336365316332366435663536303066316339386363666131666438353433343239656635653037396430392d4d6a3361585a68774166583232644653';
$apiKey = hex2bin($hex2); // xkeysib-... Mj3aXZhwAfX22dFS

$data = [
    "sender" => ["name" => "ATIERA Security", "email" => "atiera41001@gmail.com"],
    "to" => [["email" => "atiera41001@gmail.com", "name" => "Test"]],
    "subject" => "Test Brevo Delivery",
    "htmlContent" => "<h1>Test successful</h1>"
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

$r = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    echo "CURL ERROR: " . curl_error($ch) . "\n";
}
curl_close($ch);

echo "HTTP $code\n";
echo $r . "\n";
?>