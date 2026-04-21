<?php
/**
 * ATIERA Hotel & Restaurant - Central Configuration
 */

// Central Email Function (PHPMailer)
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
        // Attempt SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // Ginamit ang credentials mula sa screenshot mo
        $mail->Username   = 'linbilcelestre31@gmail.com';
        $mail->Password   = 'potivsjcwfthdzks';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
        $mail->Timeout    = 5; // Wag masyadong matagal para hindi mag-hang

        $mail->setFrom('linbilcelestre31@gmail.com', 'ATIERA Hotel');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        return $mail->send();

    } catch (Exception $e) {
        // IF SMTP BLOCKED -> FORCE NATIVE MAIL FALLBACK
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Admin <admin@atierahotelandrestaurant.com>\r\n";
        $headers .= "Reply-To: admin@atierahotelandrestaurant.com\r\n";
        
        // Pilitin nating gumamit ng PHP mail() directly
        return @mail($to, $subject, $body, $headers);
    }
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