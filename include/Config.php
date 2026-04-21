<?php
/**
 * ATIERA Central Configuration & Mail Engine
 * Version 5.2
 */

// --- 1. SMTP SERVER SETTINGS ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'linbilcelestre31@gmail.com');

/**
 * IMPORTANT: Generate a NEW App Password from your Google Account
 * (Security -> 2-Step Verification -> App Passwords)
 * Link: https://myaccount.google.com/apppasswords
 */
define('SMTP_PASS', 'poti vsjc wfth dzks'); // <--- PALITAN MO ITO NG BAGO

define('SMTP_FROM_EMAIL', 'atiera41001@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel & Restaurant');


// --- 2. BASE URL DETECTION ---
function getBaseUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $currentDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $parts = explode('/', trim($currentDir, '/'));

    if (in_array('include', $parts)) {
        $projectRoot = '/' . implode('/', array_slice($parts, 0, array_search('include', $parts)));
    } elseif (in_array('auth', $parts)) {
        $projectRoot = '/' . implode('/', array_slice($parts, 0, array_search('auth', $parts)));
    } else {
        $projectRoot = $currentDir;
    }
    return $protocol . "://" . $host . rtrim($projectRoot, '/');
}


// --- 3. PREMIUM EMAIL ENGINE ---
function sendEmail($to, $name, $subject, $body, $altBody = '')
{
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return "System Error: Mail library missing.";
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = str_replace(' ', '', SMTP_PASS);
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = SMTP_PORT;
        $mail->Timeout = 15;
        $mail->CharSet = 'UTF-8';

        // SSL Optimization for shared hosting
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;

    } catch (Exception $e) {
        $lastError = $mail->ErrorInfo;
        
        // Final Fallback: subukan ang mail() pero huwag guluhin ang code
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
        $headers .= 'From: '.SMTP_FROM_NAME.' <'.SMTP_FROM_EMAIL.'>' . "\r\n";
        
        if (@mail($to, $subject, $body, $headers)) {
            return true;
        }

        return "Email Error: " . $lastError;
    }
}