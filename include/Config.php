<?php
/**
 * ATIERA Hotel & Restaurant - Central Configuration
 * PRECISION SSL VERSION
 */

// SMTP Settings (Double-checked from your screenshot)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465); // Standard SSL Port
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'potivsjcwfthdzks'); // Ensure this matches EXACTLY
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Hotel');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__); 
    
    // Explicit PHPMailer Loading
    require_once $root . '/PHPMailer/src/Exception.php';
    require_once $root . '/PHPMailer/src/PHPMailer.php';
    require_once $root . '/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // --- SMTP Engine (Precision Handshake) ---
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        
        // Gmail requires explicit SSL for 465
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15; // Give more time for handshakes

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        // Anti-Block Options
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
         * EMERGENCY FAILOVER: NATIVE DELIVERY
         * If Gmail rejects the connection, we use the server's own official identity.
         */
        $officialEmail = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Hotel <$officialEmail>\r\n";
        $headers .= "Reply-To: $officialEmail\r\n";
        $headers .= "X-Priority: 1 (Highest)\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Return true if native mail works, else return the SMTP error info
        return @mail($to, $subject, $body, $headers, "-f$officialEmail") ?: $mail->ErrorInfo;
    }
}

// Base URL helper
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