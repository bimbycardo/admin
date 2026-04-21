<?php
/**
 * ATIERA Hotel & Restaurant - Central Configuration
 * FIREWALL BREAKER VERSION
 */

// SMTP Settings (Exact from your screenshot)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465); // Standard SSL Port
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'potivsjcwfthdzks');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Hotel');

function sendEmail($to, $name, $subject, $body)
{
    // High-Precision Path Detection
    $phpmailer_path = dirname(__DIR__) . '/PHPMailer/src/';
    
    @require_once $phpmailer_path . 'Exception.php';
    @require_once $phpmailer_path . 'PHPMailer.php';
    @require_once $phpmailer_path . 'SMTP.php';

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return "Critical Error: PHPMailer files not found at $phpmailer_path";
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // --- SMTP Engine (Aggressive SSL) ---
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'ssl'; // Match with Port 465
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 8; // Don't hang too long

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false, 
                'verify_peer_name' => false, 
                'allow_self_signed' => true
            ]
        ];

        return $mail->send();

    } catch (\Exception $e) {
        /**
         * STAGE 2: SUPER NATIVE MAIL (FIREWALL BYPASS)
         * If Gmail is blocked by the host network, we use the server's own identity.
         */
        $officialEmail = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Hotel <$officialEmail>\r\n";
        $headers .= "Reply-To: $officialEmail\r\n";
        $headers .= "Return-Path: $officialEmail\r\n";
        $headers .= "X-Priority: 1 (Highest)\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Final attempt via native mail()
        $success = @mail($to, $subject, $body, $headers, "-f$officialEmail");
        
        if (!$success) {
            error_log("Email Failed: " . $mail->ErrorInfo);
            return "Delivery Fail: " . $mail->ErrorInfo;
        }
        return true;
    }
}

// Base URL detection
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