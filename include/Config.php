<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (FINAL FIX)
 * Working SMTP configuration with Gmail
 */

// SMTP Configuration - USING CORRECT GMAIL SETTINGS
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks'); // UPDATE THIS: Use Gmail App Password
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel');

/**
 * Send email using SMTP
 * @param string $to Recipient email
 * @param string $name Recipient name
 * @param string $subject Email subject
 * @param string $body HTML email body
 * @return bool True on success, false on failure
 */
function sendEmail($to, $name, $subject, $body)
{
    // Find PHPMailer
    $root = dirname(__DIR__);
    $paths = [
        $root . '/PHPMailer/src/',
        $root . '/phpmailer/src/',
        $root . '/vendor/phpmailer/phpmailer/src/'
    ];

    $src = '';
    foreach ($paths as $path) {
        if (file_exists($path . 'PHPMailer.php')) {
            $src = $path;
            break;
        }
    }

    if (!$src) {
        error_log("PHPMailer not found at: " . implode(', ', $paths));
        return false;
    }

    // Load PHPMailer
    require_once $src . 'Exception.php';
    require_once $src . 'PHPMailer.php';
    require_once $src . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->Timeout = 30;

        // Disable SSL verification (for some hosting environments)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        // Send
        $mail->send();
        return true;

    } catch (\Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Get base URL
 */
function getBaseUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    $path = $path === '/' ? '' : $path;

    return $protocol . '://' . $host . $path;
}
?>