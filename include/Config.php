<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (STRICT GMAIL VERSION)
 */

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465); 
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'potivsjcwfthdzks');
// GMAIL REQUIREMENT: FROM MUST MATCH USERNAME
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Verification');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
    $src = '';
    foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }
    
    if(!$src) return "PHPMailer Files Missing on Server.";

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
        $mail->SMTPSecure = 'ssl'; // Locked to SSL 465
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 20;

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
        $err = $mail->ErrorInfo;
        
        // FAILOVER TO NATIVE WITH STRIPPED HEADERS
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: <".SMTP_USER.">\r\n";
        
        if (@mail($to, $subject, $body, $headers)) {
            return true;
        }
        
        return "Critical Error: SMTP Failed ($err) and Hostinger Mail Blocked Native. Target: $to";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>