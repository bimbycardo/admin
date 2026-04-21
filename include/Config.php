<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (DOMAIN FALLBACK VERSION)
 */

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465); 
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'potivsjcwfthdzks');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
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
    $recipientName = !empty($name) ? $name : 'User';

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 15;

        $mail->Priority = 1;
        $mail->addCustomHeader("X-Priority: 1 (Highest)");
        $mail->addCustomHeader("Importance: High");

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        if($mail->send()) return true;

    } catch (\Exception $e) {
        $lastErr = $mail->ErrorInfo;
        
        /**
         * DOMAIN-BASED FAILOVER
         * Hostinger often ONLY allows mail() if the From address matches the domain.
         */
        $domainEmail = 'noreply@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Security <$domainEmail>\r\n";
        $headers .= "Reply-To: $domainEmail\r\n";
        $headers .= "X-Priority: 1 (Highest)\r\n";
        
        if (@mail($to, $subject, $body, $headers, "-f$domainEmail")) {
            return true;
        }
        
        return "Delivery Blocked. SMTP: $lastErr | Native: Domain Check Failed.";
    }
}
?>