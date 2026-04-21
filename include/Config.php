<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (DIAGNOSTIC VERSION)
 */

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'potivsjcwfthdzks');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Hotel');

/**
 * Send email with Verbose Error Capture
 */
function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__);
    $paths = [
        $root . '/PHPMailer/src/',
        $root . '/phpmailer/src/',
        $root . '/vendor/phpmailer/phpmailer/src/'
    ];

    $src = '';
    foreach ($paths as $path) {
        if (file_exists($path . 'PHPMailer.php')) {
            $src = $path;
            break;
        }
    }

    if (!$src) return "PHPMailer Files Not Found. Check directory naming on server.";

    require_once $src . 'Exception.php';
    require_once $src . 'PHPMailer.php';
    require_once $src . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $debugCapture = "";

    try {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) use (&$debugCapture) {
            $debugCapture .= "$str\n";
        };

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 20;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (\Exception $e) {
        // FALLBACK TO NATIVE BUT RETURN LOG
        $officialEmail = 'admin@atierahotelandrestaurant.com';
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: ATIERA Hotel <$officialEmail>\r\n";
        
        if (@mail($to, $subject, $body, $headers, "-f$officialEmail")) {
            return true;
        }

        return "CONNECTION LOG:\n" . $debugCapture . "\nPHPMailer Error: " . $mail->ErrorInfo . "\nPHP Exception: " . $e->getMessage();
    }
}

function getBaseUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
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