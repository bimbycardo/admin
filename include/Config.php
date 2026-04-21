<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (TARGET VISIBILITY VERSION)
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
    if(!$src) return "PHPMailer Files Not Found. Folder naming issue?";

    require_once $src.'Exception.php';
    require_once $src.'PHPMailer.php';
    require_once $src.'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 15;
        $mail->SMTPOptions = ['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]];

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if($mail->send()) return true;

    } catch (\Exception $e) {
        $lastErr = $mail->ErrorInfo;
        
        // FAILOVER TO LOCAL RELAY
        try {
            $relay = new \PHPMailer\PHPMailer\PHPMailer(true);
            $relay->isSMTP();
            $relay->Host = 'localhost';
            $relay->Port = 25;
            $relay->SMTPAuth = false;
            $relay->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $relay->addAddress($to, $name);
            $relay->Subject = $subject;
            $relay->Body = $body;
            if($relay->send()) return true;
        } catch(\Exception $ex) {}

        // FAILOVER TO NATIVE
        $official = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: ATIERA <$official>\r\n";
        
        if (@mail($to, $subject, $body, $headers)) return true;
        
        return "DELIVERY FAILED for [ $to ]. SMTP Error: $lastErr | Native: Blocked.";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>