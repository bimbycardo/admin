<?php
/**
 * ATIERA — THE SOCKET BINDTO DIAGNOSTIC
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/include/Config.php';

echo "<body style='background:#0f172a; font-family:sans-serif; padding:40px;'>";
echo "<div style='max-width:800px; margin:auto; background:#1e293b; padding:30px; border-radius:10px; border:1px solid #334155;'>";
echo "<h2 style='color:#d4af37;'>🔍 BindTo IPv4 Socket Test</h2>";

$to = "atiera41001@gmail.com";
echo "<div style='background:#020617; color:#38bdf8; padding:20px; border-radius:8px; font-family:monospace; font-size:14px; white-space:pre-wrap; border-left:4px solid #38bdf8; overflow-x:auto;'>";

$root = __DIR__;
$paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
$src = '';
foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }

require_once $src . 'Exception.php';
require_once $src . 'PHPMailer.php';
require_once $src . 'SMTP.php';

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = 'echo';
    
    $mail->isSMTP(); 
    $mail->Host       = SMTP_HOST; // Must be hostname, NOT IP, to pass firewall
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587;
    $mail->Timeout    = 20;

    $mail->SMTPOptions = [
        'socket' => [
            'bindto' => '0.0.0.0:0' // FORCES IPv4 STREAM IN PHP!
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
    ];

    $mail->setFrom(SMTP_USER, 'ATIERA Test');
    $mail->addAddress($to, 'Admin Test');
    $mail->Subject = 'ATIERA Socket Bypass Test';
    $mail->Body    = 'Testing delivery via stream context forced IPv4.';

    $mail->send();
    echo "</div>";
    echo "<h3 style='color:green; margin-top:20px;'>✅ SOCKET BINDTO WORKED! Port 587 + IPv4 bypassed Hostinger Firewall!</h3>";
} catch (Exception $e) {
    echo "</div>";
    echo "<h3 style='color:red; margin-top:20px;'>❌ ERROR: " . $e->getMessage() . "</h3>";
}
echo "</div></body>";
?>
