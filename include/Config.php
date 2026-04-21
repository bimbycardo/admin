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

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // Enable logging to a file for debugging
    $logFile = $root . '/mail_log.txt';
    $mail->SMTPDebug = 2; // Output detailed logs
    $mail->Debugoutput = function($str, $level) use ($logFile) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " : " . $str . "\n", FILE_APPEND);
    };

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = str_replace(' ', '', SMTP_PASS); 
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
        $mail->Timeout    = 10;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        file_put_contents($logFile, "SUCCESS: Email sent to $to\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        $errorMsg = "FAIL: " . $mail->ErrorInfo;
        file_put_contents($logFile, $errorMsg . "\n", FILE_APPEND);
        
        // Final fallback to mail() if SMTP fails
        try {
            $mail->reset();
            $mail->isMail();
            $mail->setFrom('admin@atierahotelandrestaurant.com', SMTP_FROM_NAME);
            $mail->addAddress($to, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            if ($mail->send()) {
                file_put_contents($logFile, "FALLBACK SUCCESS: Email sent via isMail()\n", FILE_APPEND);
                return true;
            }
        } catch (Exception $e2) {
            file_put_contents($logFile, "FALLBACK FAIL: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        }
        
        return $errorMsg;
    }
}

// --- 2. BASE URL DETECTION ---
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . "://" . $host . "/admin";
    }
}