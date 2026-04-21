<?php
/**
 * ATIERA Hotel & Restaurant - Central Configuration
 * THE LAST STAND VERSION
 */

// SMTP Settings (Trying Port 587 with spaces in App Password)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587); 
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'poti vsjc wfth dzks'); // Added spaces back
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Hotel');

function sendEmail($to, $name, $subject, $body)
{
    $phpmailer_path = dirname(__DIR__) . '/PHPMailer/src/';
    require_once $phpmailer_path . 'Exception.php';
    require_once $phpmailer_path . 'PHPMailer.php';
    require_once $phpmailer_path . 'SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 20;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        return $mail->send();

    } catch (\Exception $e) {
        // FALLBACK TO NATIVE WITH ENHANCED HEADERS
        $officialEmail = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Hotel <$officialEmail>\r\n";
        $headers .= "Reply-To: $officialEmail\r\n";
        $headers .= "Return-Path: $officialEmail\r\n";
        
        // Final attempt via server's mail() function
        return @mail($to, $subject, $body, $headers, "-f$officialEmail");
    }
}

// Base URL detection
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
?>