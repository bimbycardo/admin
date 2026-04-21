<?php
/**
 * ATIERA Hotel & Restaurant - Central Configuration
 */

// Central Email Function (PHPMailer) - Ginamit ang exact code mula sa screenshot mo
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
        // --- EXACT CODE FROM YOUR SCREENSHOT ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'linbilcelestre31@gmail.com';
        $mail->Password   = 'potivsjcwfthdzks';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
        $mail->Timeout    = 20;

        // Email Identity
        $mail->setFrom('linbilcelestre31@gmail.com', 'ATIERA Hotel');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        if ($mail->send()) return true;

    } catch (Exception $e) {
        // Fallback: isMail
        try {
            $mail2 = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail2->isMail();
            $mail2->setFrom('admin@atierahotelandrestaurant.com', 'ATIERA Hotel');
            $mail2->addAddress($to, $name);
            $mail2->isHTML(true);
            $mail2->Subject = $subject;
            $mail2->Body    = $body;
            return $mail2->send();
        } catch (Exception $e2) {
            return false;
        }
    }
    return false;
}

// Base URL detection
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