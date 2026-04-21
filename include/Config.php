<?php
/**
 * ATIERA Central Configuration & Mail Engine
 * Version 5.2
 */

// --- 1. SMTP SERVER SETTINGS ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'linbilcelestre31@gmail.com');

/**
 * IMPORTANT: Generate a NEW App Password from your Google Account
 * (Security -> 2-Step Verification -> App Passwords)
 * Link: https://myaccount.google.com/apppasswords
 */
define('SMTP_PASS', 'poti vsjc wfth dzks'); // <--- PALITAN MO ITO NG BAGO

define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel & Restaurant');


// --- 2. BASE URL DETECTION ---
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


// --- 3. PREMIUM EMAIL ENGINE ---
function sendEmail($to, $name, $subject, $body, $altBody = '')
{
    // Load PHPMailer files manually
    $root = dirname(__DIR__); 
    $extPath = $root . '/PHPMailer/src/Exception.php';
    $phpPath = $root . '/PHPMailer/src/PHPMailer.php';
    $smtPath = $root . '/PHPMailer/src/SMTP.php';

    if (file_exists($phpPath)) {
        require_once $extPath;
        require_once $phpPath;
        require_once $smtPath;
    }

    $lastError = 'SMTP Blocked by Host';
    
    // --- TRY SMTP (GMAIL) ---
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $portsToTry = [587, 465]; 
        foreach ($portsToTry as $port) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = str_replace(' ', '', SMTP_PASS); 
                $mail->SMTPSecure = ($port == 465) ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $port;
                $mail->Timeout    = 5; // Fast timeout if blocked
                $mail->CharSet    = 'UTF-8';

                $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($to, $name);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = $altBody ?: strip_tags($body);

                $mail->send();
                return true; 
            } catch (Exception $e) {
                $lastError = $mail->ErrorInfo;
                // If network is blocked, skip to mail() immediately
                if (strpos($lastError, 'unreachable') !== false) break;
            }
        }
    }

    // --- FALLBACK: NATIVE PHP MAIL (The Unblocker) ---
    // Note: We use a generic sender name because Google blocks @gmail.com from non-Google servers
    $sender_email = 'no-reply@' . $_SERVER['HTTP_HOST'];
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . SMTP_FROM_NAME . ' <' . $sender_email . '>' . "\r\n";
    $headers .= 'Reply-To: ' . SMTP_USER . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();

    if (@mail($to, $subject, $body, $headers, "-f" . $sender_email)) {
        return true; 
    }

    return "All methods failed. SMTP Blocked & mail() ignored.";
}