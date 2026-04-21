<?php
/**
 * ATIERA Central Configuration & Mail Engine
 * Version 5.2
 */

// --- 1. SMTP SERVER SETTINGS ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel & Restaurant');

/**
 * IMPORTANT: Generate a NEW App Password from your Google Account
 * (Security -> 2-Step Verification -> App Passwords)
 * Link: https://myaccount.google.com/apppasswords
 */
define('SMTP_PASS', 'poti vsjc wfth dzks'); // <--- PALITAN MO ITO NG BAGO


// --- 2. BASE URL DETECTION ---
function getBaseUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $currentDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
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

// --- 3. PREMIUM EMAIL API (Brevo) ---
// Sign up for a free account at brevo.com if native mail() fails.
define('BREVO_API_KEY', 'PASTE_YOUR_API_KEY_HERE');

function sendEmail($to, $name, $subject, $body, $altBody = '')
{
    $root = dirname(__DIR__); 
    require_once $root . '/PHPMailer/src/Exception.php';
    require_once $root . '/PHPMailer/src/PHPMailer.php';
    require_once $root . '/PHPMailer/src/SMTP.php';

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return "PHPMailer Library Not Found.";
    }

    $lastError = '';
    
    // --- TRY SMTP PORTS (ALL COMBINATIONS) ---
    $configs = [
        ['port' => 587, 'secure' => 'tls'],
        ['port' => 465, 'secure' => 'ssl'],
        ['port' => 587, 'secure' => ''],    // No encryption (sometimes bypasses firewall)
        ['port' => 25,  'secure' => ''],    // Standard port 25
        ['port' => 2525, 'secure' => 'tls'] // Alternative port 2525
    ];

    foreach ($configs as $cfg) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = str_replace(' ', '', SMTP_PASS); 
            $mail->Port       = $cfg['port'];
            $mail->SMTPSecure = $cfg['secure'];
            $mail->Timeout    = 5; 
            $mail->CharSet    = 'UTF-8';
            
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
            ];

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return true; 
        } catch (Exception $e) {
            $lastError = "Port {$cfg['port']} ({$cfg['secure']}): " . $mail->ErrorInfo;
            continue; 
        }
    }

    // --- TRY INTERNAL SENDMAIL (Highest bypass chance for PHPMailer) ---
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        if (file_exists('/usr/sbin/sendmail')) {
            $mail->isSendmail();
        } else {
            $mail->isMail();
        }

        $domain = $_SERVER['HTTP_HOST'] ?? 'atierahotelandrestaurant.com';
        $mail->setFrom('admin@' . $domain, SMTP_FROM_NAME);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true; 
    } catch (Exception $e) {
        $lastError = "Sendmail/Mail: " . $mail->ErrorInfo;
    }

    // --- TRY SMTP PORTS AS LAST RESORT ---
    $configs = [
        ['port' => 465, 'secure' => 'ssl'],
        ['port' => 587, 'secure' => 'tls'],
        ['port' => 25,  'secure' => '']
    ];

    foreach ($configs as $cfg) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = str_replace(' ', '', SMTP_PASS); 
            $mail->Port       = $cfg['port'];
            $mail->SMTPSecure = $cfg['secure'];
            $mail->Timeout    = 5; 
            
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
            ];

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return true; 
        } catch (Exception $e) {
            continue; 
        }
    }

    return "All PHPMailer methods failed. Host is completely blocking outbound mail.";
}