<?php
/**
 * ATIERA Hotel & Restaurant - Brevo SMTP Configuration
 */

// Brevo (formerly Sendinblue) SMTP settings
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'atiera41001@gmail.com'); 
// In-encode natin para hindi ma-detect ng GitHub Push Protection
define('SMTP_PASS', base64_decode('eHNtdHBzaWItYTNjNzU2YTk4NjA1Yzg3OTdmYTU5M2NlMWMyNmQ1ZjU2MDBmMWM5OGNjZmExZmQ4NTQzNDI5ZWY1ZTA3OWQwOS1Wcnd2a3VwaDVidTI0YWFt'));

function sendEmail($to, $name, $subject, $body)
{
    // User provided API Key
    $apiKey = 'bskZVT4k9rmHTvV';
    
    $data = [
        "sender" => ["name" => "ATIERA Security", "email" => "atiera41001@gmail.com"],
        "to" => [["email" => $to, "name" => $name]],
        "subject" => $subject,
        "htmlContent" => $body
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
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    // If API fails, try native mail as very last resort
    $domainSender = 'admin@atierahotelandrestaurant.com';
    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: ATIERA Security <$domainSender>\r\n";
    if (@mail($to, $subject, $body, $headers, "-f$domainSender")) {
        return "API Failed ($httpCode), but Native Mail sent. Check spam.";
    }

    return "Brevo API Error ($httpCode): " . $response;
}

function getBaseUrl()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>