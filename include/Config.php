<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (ATOMIC DEBUG)
 * THIS VERSION WILL ECHO ERRORS DIRECTLY TO SCREEN
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465); // Standard SSL
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'potivsjcwfthdzks');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Hotel');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    
    // Explicit Path Checking
    $possible_paths = [
        $root . '/PHPMailer/src/',
        $root . '/phpmailer/src/',
        $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/',
        $_SERVER['DOCUMENT_ROOT'] . '/phpmailer/src/'
    ];

    $found_path = '';
    foreach ($possible_paths as $p) {
        if (file_exists($p . 'PHPMailer.php')) {
            $found_path = $p;
            break;
        }
    }

    if (!$found_path) {
        $err = "FATAL ERROR: PHPMailer files not found in any of these locations: " . implode(', ', $possible_paths);
        echo "<div style='color:red; background:white; padding:20px; border:5px solid red; z-index:9999; position:relative;'>$err</div>";
        return $err;
    }

    require_once $found_path . 'Exception.php';
    require_once $found_path . 'PHPMailer.php';
    require_once $found_path . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $transcript = "";

    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 3; // MAX DEBUG
        $mail->Debugoutput = function($str, $level) use (&$transcript) {
            $transcript .= $str . "<br>";
        };

        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 20;

        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if($mail->send()) return true;

    } catch (\Exception $e) {
        // FAILOVER TO NATIVE
        $officialEmail = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: ATIERA Hotel <$officialEmail>\r\n";
        
        if (@mail($to, $subject, $body, $headers, "-f$officialEmail")) return true;

        $full_error = "<b>SMTP TRANSCRIPT:</b><br>$transcript<br><b>EXCEPTION:</b> " . $e->getMessage();
        echo "<div style='color:darkred; background:#fff0f0; padding:15px; border:2px solid red; font-family:monospace; font-size:12px;'>$full_error</div>";
        return $full_error;
    }
}

function getBaseUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    if ($path === '\\' || $path === '/') $path = '';
    return $protocol . "://" . $host . $path;
}
?>