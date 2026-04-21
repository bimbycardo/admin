<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (THE HOLY GRAIL FIX)
 * Resolves Error 101 (IPv6 Network Unreachable) AND Error 111 (Port 465 Block)
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // STARTTLS Port
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks'); 
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Security');

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
    
    try {
        $mail->isSMTP(); 
        
        // CRITICAL FIX FOR ERROR 101: Force IPv4 resolution
        // Bypasses the broken IPv6 network routing on Hostinger
        $mail->Host       = gethostbyname(SMTP_HOST); 
        
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Port 587 Must use TLS
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 20;

        // Bypasses certificate mismatch when connecting via direct IP Address
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) {
            return true;
        }

    } catch (\Exception $e) {
        $err = $mail->ErrorInfo;
        return "SMTP Blocked: $err";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>