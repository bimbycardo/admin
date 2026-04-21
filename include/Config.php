<?php
/**
 * ATIERA Hotel & Restaurant - Central Configuration
 */

// SMTP Settings (Gayahin ang details sa screenshot mo)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'potivsjcwfthdzks');
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel');

/**
 * Central Email Function (PHPMailer)
 * This is the ONLY place that handles sending emails.
 */
function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__); 
    @include_once $root . '/PHPMailer/src/Exception.php';
    @include_once $root . '/PHPMailer/src/PHPMailer.php';
    @include_once $root . '/PHPMailer/src/SMTP.php';

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return "PHPMailer Load Error.";
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // --- SMTP Engine ---
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 5;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        return $mail->send();

    } catch (Exception $e) {
        /**
         * FALLBACK: OFFICIAL DOMAIN MAIL
         * If Gmail is blocked by the host, we use native mail with domain identity.
         */
        $officialEmail = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Hotel <$officialEmail>\r\n";
        $headers .= "Reply-To: $officialEmail\r\n";
        $headers .= "Return-Path: $officialEmail\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return @mail($to, $subject, rtrim($body), $headers, "-f$officialEmail");
    }
}

// Base URL detection helper
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