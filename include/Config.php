<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (PHPMailer isMail FIX)
 * PROOF: Connection refused (111) means Hostinger permanently blocks standard outbound SMTP.
 * FIX: Using perfectly formatted native mail via PHPMailer with a valid Return-Path.
 */

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');

// MUST use the domain currently hosted on Hostinger.
// Do NOT use @gmail.com here, otherwise Hostinger silently drops it to prevent spoofing.
define('OFFICIAL_SENDER', 'admin@atierahotelandrestaurant.com');
define('OFFICIAL_NAME', 'ATIERA Security');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
    $src = '';
    foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }
    
    if(!$src) return "PHPMailer Missing.";

    require_once $src.'Exception.php';
    require_once $src.'PHPMailer.php';
    require_once $src.'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $recipientName = !empty($name) ? $name : 'User';

    try {
        // USE NATIVE MAIL (Since external SMTP 465/587 is blocked)
        $mail->isMail(); 

        // CRITICAL: The sender address MUST belong to this domain.
        $mail->setFrom(OFFICIAL_SENDER, OFFICIAL_NAME);
        
        // CRITICAL FOR GMAIL: Prevents sending as "nobody@server.hostinger.com"
        // This is exactly why it was being silently deleted before reaching your Spam folder.
        $mail->Sender = OFFICIAL_SENDER;
        
        // Priority headers
        $mail->addCustomHeader("X-Priority: 1 (Highest)");
        
        $mail->addAddress($to, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();

    } catch (\Exception $e) {
        $error = $mail->ErrorInfo;
        return "Mail Deliverability Blocked by Host: $error";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>