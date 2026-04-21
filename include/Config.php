<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (LIVE LOGGER VERSION)
 */

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $logFile = $root . '/em_log.txt';
    $time = date('Y-m-d H:i:s');
    
    // We log everything so test_phpmailer.php can read it
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
        
        // Let's force your Gmail address as the sender.
        // If Hostinger drops it, there will be no PHP error, but it won't arrive.
        $mail->setFrom(SMTP_USER, 'ATIERA Security');
        
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) {
            file_put_contents($logFile, "[$time] ✅ SUCCESS: PHP generated no errors. Email handed to Hostinger Mail system successfully.\n", FILE_APPEND);
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