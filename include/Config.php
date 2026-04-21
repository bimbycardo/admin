<?php
/**
 * ATIERA Hotel & Restaurant - Central Configuration
 * ULTIMATE STABILITY VERSION
 */

// SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // Testing 587 with TLS for better firewall penetration
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks'); // App Password (Ensure no spaces)
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__); 
    
    // Explicit Loading
    require_once $root . '/PHPMailer/src/Exception.php';
    require_once $root . '/PHPMailer/src/PHPMailer.php';
    require_once $root . '/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // --- SMTP Engine ---
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        
        // Auto-detect secure mode
        $mail->SMTPSecure = (SMTP_PORT == 465) ? 'ssl' : 'tls'; 
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 10; // Medium timeout

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        return $mail->send();

    } catch (\Exception $e) {
        /**
         * FALLBACK: OFFICIAL DOMAIN MAIL
         * If Gmail is blocked, we force a high-priority native mail delivery.
         */
        $officialEmail = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Hotel <$officialEmail>\r\n";
        $headers .= "Reply-To: $officialEmail\r\n";
        $headers .= "Return-Path: $officialEmail\r\n";
        $headers .= "X-Priority: 1 (Highest)\r\n";
        
        // Return error message on real failure to help debugging
        if (!@mail($to, $subject, $body, $headers, "-f$officialEmail")) {
            return "SMTP/Native Error: " . $mail->ErrorInfo;
        }
        return true;
    }
}

// Base URL detection helper
function getBaseUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $currentDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
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
?>