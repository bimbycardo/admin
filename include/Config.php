<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (LOCAL RELAY BYPASS)
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
        // --- ATTEMPT 1: LOCAL RELAY (Fastest on Hostinger) ---
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 25;
        $mail->SMTPAuth = false; // Internal relay doesn't need auth
        $mail->setFrom('noreply@atierahotelandrestaurant.com', 'ATIERA Security');
        $mail->addAddress($to, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        if($mail->send()) return true;

    } catch (\Exception $e) {
        // --- ATTEMPT 2: GOOGLE SMTP (Fallback) ---
        try {
            $gmail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $gmail->isSMTP();
            $gmail->Host = SMTP_HOST;
            $gmail->SMTPAuth = true;
            $gmail->Username = SMTP_USER;
            $gmail->Password = SMTP_PASS;
            $gmail->SMTPSecure = 'ssl';
            $gmail->Port = 465;
            $gmail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $gmail->addAddress($to, $recipientName);
            $gmail->isHTML(true);
            $gmail->Subject = $subject;
            $gmail->Body = $body;
            $gmail->SMTPOptions = ['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]];
            if($gmail->send()) return true;
        } catch (\Exception $ex) {
            // --- ATTEMPT 3: NATIVE MAIL (Absolute Last Resort) ---
            $domainEmail = 'noreply@atierahotelandrestaurant.com';
            $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: ATIERA Security <$domainEmail>\r\n";
            if (@mail($to, $subject, $body, $headers, "-f$domainEmail")) return true;
            
            return "All paths failed. Error: " . $ex->getMessage();
        }
    }
}
?>