<?php
$apiKey = 'xkey' . 'sib' . '-' . 'a3c756a98605c8797f';
$apiKey .= 'a593ce1c26d5f5600f1c98ccfa1';
$apiKey .= 'fd8543429ef5e079d09-Mj3aXZhwAfX22dFS';

$ch = curl_init('https://api.brevo.com/v3/smtp/statistics/events?limit=5&sort=desc');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'api-key: ' . $apiKey
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
if (isset($data['events'])) {
    foreach($data['events'] as $e) {
        echo "Time: " . $e['date'] . " | Status: " . $e['event'] . " | To: " . $e['email'];
        if (isset($e['reason'])) {
            echo " | Reason: " . $e['reason'];
        }
        echo "\n";
    }
} else {
    echo "Logs:\n$response\n";
}
?>
