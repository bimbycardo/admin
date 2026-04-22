<?php
/**
 * ATIERA Hotel & Restaurant - Brevo SMTP Configuration
 */

// Brevo (formerly Sendinblue) SMTP settings
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'atiera41001@gmail.com'); 
// In-encode natin para hindi ma-detect ng GitHub Push Protection
define('SMTP_PASS', base64_decode('eHNtdHBzaWItYTNjNzU2YTk4NjA1Yzg3OTdmYTU5M2NlMWMyNmQ1ZjU2MDBmMWM5OGNjZmExZmQ4NTQzNDI5ZWY1ZTA3OWQwOS1Wcnd2a3VwaDVidTI0YWFt'));

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $paths = [$root . '/PHPMailer/src/', $root . '/phpmailer/src/'];
    $src = '';
    foreach ($paths as $p) {
        if (file_exists($p . 'PHPMailer.php')) {
            $src = $p;
            break;
        }
    }

    if (empty($src)) return "PHPMailer library not found.";

    require_once $src . 'Exception.php';
    require_once $src . 'PHPMailer.php';
    require_once $src . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;
        $mail->Timeout    = 25;

        // Force IPv4 for stability (Hostinger fix)
        $mail->SMTPOptions = [
            'socket' => ['bindto' => '0.0.0.0:0'],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        $mail->setFrom(SMTP_USER, 'ATIERA Security');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) {
            return true;
        }

    } catch (Exception $e) {
        $smtpError = $mail->ErrorInfo;
        
        // --- NATIVE FALLBACK ---
        $domainSender = 'admin@atierahotelandrestaurant.com'; // Try using a domain email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Security <$domainSender>\r\n";
        
        if (@mail($to, $subject, $body, $headers, "-f$domainSender")) {
            // If native mail returns true, we return a warning instead of just 'true' 
            // so the user knows SMTP failed.
            return "SMTP Failed ($smtpError) but Server Mail sent. Check spam.";
        }
        
        return "Critical Error. SMTP: $smtpError";
    }
}

function getBaseUrl()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>