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
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

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
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function getBaseUrl()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>