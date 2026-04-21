<?php
/**
 * ATIERA Hotel & Restaurant - Configuration File
 */

// --- 1. EMAIL CONFIGURATION (PHPMailer Focus) ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'poti vsjc wfth dzks');
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel & Restaurant');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__); 
    @include_once $root . '/PHPMailer/src/Exception.php';
    @include_once $root . '/PHPMailer/src/PHPMailer.php';
    @include_once $root . '/PHPMailer/src/SMTP.php';

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return "Critical Error: PHPMailer missing.";
    }

    // Try SMTP First
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = str_replace(' ', '', SMTP_PASS); 
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
        $mail->Timeout    = 5;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if ($mail->send()) return true;
    } catch (Exception $e) {
        // Fallback to Native Mail if SMTP fails
        try {
            $mail2 = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail2->isMail();
            $mail2->setFrom('admin@atierahotelandrestaurant.com', SMTP_FROM_NAME);
            $mail2->addAddress($to, $name);
            $mail2->isHTML(true);
            $mail2->Subject = $subject;
            $mail2->Body    = $body;
            if ($mail2->send()) return true;
        } catch (Exception $e2) {
            return "Fail: " . $mail2->ErrorInfo;
        }
    }
    return "All methods failed.";
}

// --- 2. BASE URL DETECTION ---
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . "://" . $host . "/admin";
    }
}