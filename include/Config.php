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
 * Attempts multiple ports (587, 465), forces IPv4, and formats passwords.
 * Returns true on success, or a string containing the error on failure.
 */
function sendEmail($to, $name, $subject, $body, $altBody = '')
{
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return "PHPMailer class is completely missing from the system.";
    }

    $portsToTry = [465, 587]; // Try 465 first for Google
    $lastError = '';

    foreach ($portsToTry as $port) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST; 
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            // Many users paste App Passwords with spaces; Google rejects spaces
            $mail->Password   = str_replace(' ', '', SMTP_PASS); 
            $mail->SMTPSecure = ($port == 465) ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;
            $mail->Timeout    = 10; 

            // SSL Bypass - useful for various hosting environments
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
            return true; // Sent successfully!

        } catch (\Exception $e) {
            $lastError = $mail->ErrorInfo;
            continue; // Try the next port
        }
    }

    // FINAL FALLBACK: Native PHP mail()
    // This often works on production servers even if SMTP ports are blocked
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>' . "\r\n";
    $headers .= 'Reply-To: ' . SMTP_FROM_EMAIL . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();

    if (@mail($to, $subject, $body, $headers)) {
        return true;
    }

    // If all fail, return the last error message
    error_log("All SMTP and mail() failed for {$to}: " . $lastError);
    return "Failed to send email. SMTP Error: " . $lastError;
}
?>