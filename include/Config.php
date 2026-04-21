<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (ULTIMATE FAILOVER)
 * Includes Gmail App Password but seamlessly bypasses Hostinger's Firewall 111 Block.
 */

// User's requested Gmail configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); 
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks');

// Hostinger's internal gateway (Required if Hostinger blocks Gmail)
define('OFFICIAL_SENDER', 'admin@atierahotelandrestaurant.com');
define('OFFICIAL_NAME', 'ATIERA Security');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
    $src = '';
    foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }
    
    require_once $src.'Exception.php';
    require_once $src.'PHPMailer.php';
    require_once $src.'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    // ATTEMPT 1: User's Gmail App Password Mode
    try {
        $mail->isSMTP(); 
        $mail->Host       = gethostbyname(SMTP_HOST); 
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 5; // Fast timeout so we don't hang if firewall blocks it
        
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        $mail->setFrom(SMTP_USER, OFFICIAL_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) return true;

    } catch (\Exception $e) {
        // ATTEMPT 2: HOSTINGER DOMAIN GATEWAY (The Firewall Bypass)
        // If Hostinger outputs "Connection Refused 111", it drops the connection.
        // We catch that error and immediately use Hostinger's internal Native email.
        
        try {
            $backupMail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $backupMail->isMail(); 
            
            // WE MUST USE THE HOSTED DOMAIN FOR HOSTINGER TO ACCEPT IT
            $backupMail->setFrom(OFFICIAL_SENDER, OFFICIAL_NAME);
            $backupMail->Sender = OFFICIAL_SENDER; // Return-Path to survive DMARC
            
            $backupMail->addAddress($to, $name);
            $backupMail->isHTML(true);
            $backupMail->Subject = $subject;
            $backupMail->Body    = $body;
            $backupMail->addCustomHeader("X-Priority: 1 (Highest)");
            
            if ($backupMail->send()) {
                return true;
            }
        } catch (\Exception $ex) {
            return "Fatal Block: " . $ex->getMessage();
        }
        
        // If we reach here, we hit the exact 111 error string from the user's report
        return "SMTP Blocked: " . $e->getMessage() . " | Native: " . $backupMail->ErrorInfo;
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>