<?php
// Central SMTP Configuration
// Change these values to use a different sender email account

define('SMTP_HOST', 'smtp.gmail.com');

define('SMTP_PORT', 465); // Changed to 465 to bypass firewall/ISP blocks
define('SMTP_USER', 'atiera41001@gmail.com'); // Put your random email here
define('SMTP_PASS', 'tmtu gklv rkbn arpz');    // Put your random email App Password here
define('SMTP_FROM_EMAIL', 'atiera41001@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel');

// Base URL detection (helps with subdomains)
function getBaseUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $currentDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Go up levels until we reach the 'admin' or project root
    // For admin.atierahotelandrestaurant.com/admin/include/Settings.php, we want /admin/
    $parts = explode('/', trim($currentDir, '/'));
    if (in_array('include', $parts)) {
        $projectRoot = '/' . implode('/', array_slice($parts, 0, array_search('include', $parts)));
    } elseif (in_array('auth', $parts)) {
        $projectRoot = '/' . implode('/', array_slice($parts, 0, array_search('auth', $parts)));
    } else {
        $projectRoot = $currentDir;
    }
    return $protocol . "://" . $host . rtrim($projectRoot, '/');
}

/**
 * Robust Email Sender
 * Uses strictly SMTP to ensure reliable delivery and accurate error reporting.
 */
function sendEmail($to, $name, $subject, $body, $altBody = '')
{
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST; 
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = (SMTP_PORT == 465) ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 15; 

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;
    } catch (\Exception $e) {
        // Record the actual Mailer error directly so we can diagnose connection issues
        error_log("SMTP Email strictly failed for {$to}: " . $mail->ErrorInfo);
        return false;
    }
}
?>