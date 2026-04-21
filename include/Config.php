<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (Backend Only)
 * No debug output, pure email functionality
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks');
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel');

/**
 * Send email using SMTP
 * @param string $to Recipient email
 * @param string $name Recipient name
 * @param string $subject Email subject
 * @param string $body HTML email body
 * @return bool|string True on success, error message on failure
 */
function sendEmail($to, $name, $subject, $body)
{
    // Auto-detect PHPMailer path
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
        return "PHPMailer not found. Please install PHPMailer library.";
    }

    // Load PHPMailer classes
    require_once $src . 'Exception.php';
    require_once $src . 'PHPMailer.php';
    require_once $src . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port = SMTP_PORT;
        $mail->Timeout = 30;

        // Sender & Recipient
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body); // Plain text fallback

        // SSL Options (disable verification for development only)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Send email
        $mail->send();
        return true;

    } catch (\Exception $e) {
        // Return error message
        return "Email failed: " . $mail->ErrorInfo;
    }
}

/**
 * Get base URL of the application
 * @return string Base URL
 */
function getBaseUrl()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];

    // Remove the last part (filename) to get directory
    $path = dirname($scriptName);

    // Normalize path
    $path = str_replace('\\', '/', $path);
    $path = ($path === '/') ? '' : $path;

    return $protocol . "://" . $host . $path;
}
?>