<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (NUCLEAR OPTION)
 */

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465); 
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'potivsjcwfthdzks');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Hotel');

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

    try {
        // LAYER 1: Standard SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 10;
        $mail->SMTPOptions = ['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]];

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if($mail->send()) return true;

    } catch (\Exception $e) {
        $lastErr = $mail->ErrorInfo;
        
        // LAYER 2: Try Local Relay (Common on Hostinger)
        try {
            $mail->reset();
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->Port = 25;
            $mail->SMTPAuth = false;
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to, $name);
            $mail->Subject = $subject;
            $mail->Body = $body;
            if($mail->send()) return true;
        } catch(\Exception $ex) {}

        // LAYER 3: Fortified Native Mail (The Survivor)
        $official = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Hotel <$official>\r\n";
        $headers .= "Reply-To: $official\r\n";
        $headers .= "X-Priority: 1 (Highest)\r\n";
        
        if (@mail($to, $subject, $body, $headers, "-f$official")) return true;
        
        return "All Delivery Layers Failed. SMTP: $lastErr | Native: Fail";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>