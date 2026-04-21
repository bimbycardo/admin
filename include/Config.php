<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (GMAIL APP PASSWORD VERSION)
 * Uses Port 587 (STARTTLS) which is typically whitelisted on strict shared hosts.
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // Crucial: 587 is usually open when 465 is blocked
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks'); // The Google App Password 
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Security');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $logFile = $root . '/em_log.txt';
    $time = date('Y-m-d H:i:s');
    
    file_put_contents($logFile, "[$time] Attempting SMTP to $to...\n", FILE_APPEND);

    $paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
    $src = '';
    foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }
    
    require_once $src.'Exception.php';
    require_once $src.'PHPMailer.php';
    require_once $src.'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP(); 
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // TLS for 587
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 20;

        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) {
            file_put_contents($logFile, "[$time] ✅ SUCCESS: Verified Gmail SMTP delivery.\n", FILE_APPEND);
            return true;
        }

    } catch (\Exception $e) {
        $err = $mail->ErrorInfo;
        file_put_contents($logFile, "[$time] ❌ ERROR: Gmail SMTP Blocked - $err\n", FILE_APPEND);
        return "SMTP Blocked: $err";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>