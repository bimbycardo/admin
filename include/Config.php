<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (SOCKET BINDTO FIX)
 * Uses the advanced bindto socket parameter to force IPv4 WITHOUT changing the hostname.
 */

// User's requested Gmail configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); 
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
    $src = '';
    foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }
    
    require_once $src.'Exception.php';
    require_once $src.'PHPMailer.php';
    require_once $src.'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP(); 
        
        // Use normal hostname (Firewall whitelisted by Hostinger usually)
        $mail->Host       = SMTP_HOST; 
        
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 15;
        
        // CRITICAL MAGIC: 'bindto' => '0.0.0.0:0' forces the PHP socket to use IPv4 safely.
        // This stops Error 101 (IPv6 Failure) AND stops Error 111 (Direct IP block).
        $mail->SMTPOptions = [
            'socket' => [
                'bindto' => '0.0.0.0:0'
            ],
            'ssl' => [
                'verify_peer' => false, 
                'verify_peer_name' => false, 
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(SMTP_USER, 'ATIERA Security');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) return true;

    } catch (\Exception $e) {
        $err = $e->getMessage();
        // If Hostinger absolutely destroys SMTP, fall back to native Mail
        $domainSender = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Security <$domainSender>\r\n";
        
        if (@mail($to, $subject, $body, $headers, "-f$domainSender")) {
            return true;
        }
        
        return "Bypass Blocked. SMTP Error: $err";
    }
}

function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>