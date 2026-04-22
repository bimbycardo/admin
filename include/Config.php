<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (SOCKET BINDTO FIX)
 * Uses the advanced bindto socket parameter to force IPv4 WITHOUT changing the hostname.
 */

// User's requested Gmail configuration has been moved directly inside the sendEmail function below for a cleaner, encapsulated setup.

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    // Search for PHPMailer in common locations
    $paths = [$root . '/PHPMailer/src/', $root . '/phpmailer/src/'];
    $src = '';
    foreach ($paths as $p) {
        if (file_exists($p . 'PHPMailer.php')) {
            $src = $p;
            break;
        }
    }

    // Fallback if not found
    if (empty($src)) {
        return "PHPMailer library not found.";
    }

    require_once $src . 'Exception.php';
    require_once $src . 'PHPMailer.php';
    require_once $src . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'atiera41001@gmail.com';
        $mail->Password   = 'dxis mokl icnb iemt'; // App password
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // Switched to 465/SSL
        $mail->Port       = 465;
        $mail->Timeout    = 15;

        // CRITICAL FIX: Force IPv4 and handle SSL certificate issues
        $mail->SMTPOptions = [
            'socket' => [
                'bindto' => '0.0.0.0:0'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom('atiera41001@gmail.com', 'ATIERA Security');
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (\Exception $e) {
        // FALLBACK: If Hostinger blocks SMTP (Error 111/101), use Native PHP Mail
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Security <atiera41001@gmail.com>\r\n";
        
        if (@mail($to, $subject, $body, $headers)) {
            return true; 
        }
        
        return "Both SMTP and Native Mail failed. SMTP Error: {$mail->ErrorInfo}";
    }
}

function getBaseUrl()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>