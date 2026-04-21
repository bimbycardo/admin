<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (HOSTINGER INTERNAL RELY)
 * PROOF: External SMTP is blocked (Error 101), so we use Internal Relay.
 */

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'localhost'); // Hostinger internal relay
if (!defined('SMTP_PORT')) define('SMTP_PORT', 25);
if (!defined('SMTP_USER')) define('SMTP_USER', ''); // Internal often doesn't need auth
if (!defined('SMTP_PASS')) define('SMTP_PASS', ''); 
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'noreply@atierahotelandrestaurant.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Security');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
    $src = '';
    foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }
    if(!$src) return "PHPMailer Missing.";

    require_once $src.'Exception.php';
    require_once $src.'PHPMailer.php';
    require_once $src.'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $recipientName = !empty($name) ? $name : 'Administrator';

    try {
        // --- STEP 1: ATTEMPT INTERNAL RELAY (THE BYPASS) ---
        $mail->isSMTP();
        $mail->Host       = 'localhost'; 
        $mail->SMTPAuth   = false;
        $mail->Port       = 25;
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if($mail->send()) return true;

    } catch (\Exception $e) {
        // --- STEP 2: TOTAL FALLBACK TO NATIVE MAIL ---
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Security <".SMTP_FROM_EMAIL.">\r\n";
        $headers .= "Reply-To: ".SMTP_FROM_EMAIL."\r\n";
        $headers .= "X-Priority: 1 (Highest)\r\n";
        
        if (@mail($to, $subject, $body, $headers, "-f".SMTP_FROM_EMAIL)) {
            return true;
        }
        
        return "Critical: All paths blocked by Hostinger Firewall.";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>