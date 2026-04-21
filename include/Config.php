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
        return "Critical Error: PHPMailer library missing.";
    }

    $errors = [];

    // --- TRY METHOD 1: Port 465 (SSL) ---
    try {
        $mail1 = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail1->isSMTP();
        $mail1->Host       = SMTP_HOST;
        $mail1->SMTPAuth   = true;
        $mail1->Username   = SMTP_USER;
        $mail1->Password   = str_replace(' ', '', SMTP_PASS); 
        $mail1->SMTPSecure = 'ssl';
        $mail1->Port       = 465;
        $mail1->Timeout    = 5;
        $mail1->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail1->addAddress($to, $name);
        $mail1->isHTML(true);
        $mail1->Subject = $subject;
        $mail1->Body    = $body;
        if ($mail1->send()) return true;
    } catch (Exception $e) {
        $errors[] = "Port 465 Fail: " . $mail1->ErrorInfo;
    }

    // --- TRY METHOD 2: Port 587 (TLS) ---
    try {
        $mail2 = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail2->isSMTP();
        $mail2->Host       = SMTP_HOST;
        $mail2->SMTPAuth   = true;
        $mail2->Username   = SMTP_USER;
        $mail2->Password   = str_replace(' ', '', SMTP_PASS); 
        $mail2->SMTPSecure = 'tls';
        $mail2->Port       = 587;
        $mail2->Timeout    = 5;
        $mail2->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail2->addAddress($to, $name);
        $mail2->isHTML(true);
        $mail2->Subject = $subject;
        $mail2->Body    = $body;
        if ($mail2->send()) return true;
    } catch (Exception $e) {
        $errors[] = "Port 587 Fail: " . $mail2->ErrorInfo;
    }

    // --- TRY METHOD 3: Native isMail() ---
    try {
        $mail3 = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail3->isMail();
        $mail3->setFrom('admin@atierahotelandrestaurant.com', SMTP_FROM_NAME);
        $mail3->addAddress($to, $name);
        $mail3->isHTML(true);
        $mail3->Subject = $subject;
        $mail3->Body    = $body;
        if ($mail3->send()) return true;
    } catch (Exception $e) {
        $errors[] = "isMail Fail: " . $mail3->ErrorInfo;
    }

    return "SMTP Final Error: " . implode(" | ", $errors);
}

// --- 2. BASE URL DETECTION ---
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . "://" . $host . "/admin";
    }
}