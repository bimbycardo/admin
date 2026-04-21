<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (FINAL NATIVE FIX)
 */

// DO NOT USE GMAIL AS SENDER FOR NATIVE MAIL. HOSTINGER DROPS IT SILENTLY.
// WE MUST USE THE OFFICIAL HOSTED DOMAIN FOR THE SYSTEM TO ACCEPT IT.
define('OFFICIAL_SENDER', 'admin@atierahotelandrestaurant.com');
define('OFFICIAL_NAME', 'ATIERA Security');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $logFile = $root . '/em_log.txt';
    $time = date('Y-m-d H:i:s');
    
    file_put_contents($logFile, "[$time] Attempting to send to $to...\n", FILE_APPEND);

    $paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
    $src = '';
    foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }
    
    if(!$src) {
        file_put_contents($logFile, "[$time] ❌ ERROR: PHPMailer missing.\n", FILE_APPEND);
        return "PHPMailer Missing.";
    }

    require_once $src.'Exception.php';
    require_once $src.'PHPMailer.php';
    require_once $src.'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isMail(); 
        
        // CRITICAL FIX: The sender MUST be the domain hosted on Hostinger.
        // If this is set to @gmail.com, Hostinger silently deletes the email as anti-spoofing!
        $mail->setFrom(OFFICIAL_SENDER, OFFICIAL_NAME);
        
        // This is the Return-Path. It prevents Gmail from throwing it into the void.
        $mail->Sender = OFFICIAL_SENDER; 
        
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) {
            file_put_contents($logFile, "[$time] ✅ SUCCESS: Email accepted by server using OFFICIAL_SENDER (" . OFFICIAL_SENDER . ").\n", FILE_APPEND);
            return true;
        } else {
            file_put_contents($logFile, "[$time] ❌ FAILED: Unknown Mail Failure.\n", FILE_APPEND);
            return "Mail Failed.";
        }

    } catch (\Exception $e) {
        $err = $mail->ErrorInfo;
        file_put_contents($logFile, "[$time] ❌ ERROR: $err\n", FILE_APPEND);
        return "PHPMailer Error: $err";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>