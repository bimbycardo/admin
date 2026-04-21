<?php
/**
 * ATIERA — LIVE GMAIL SMTP SIMULATOR
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/include/Config.php';

echo "<body style='background:#0f172a; font-family:sans-serif; padding:40px;'>";
echo "<div style='max-width:800px; margin:auto; background:#1e293b; padding:30px; border-radius:10px; border:1px solid #334155;'>";
echo "<h2 style='color:#d4af37;'>🔍 Gmail SMTP (App Password) Live Test</h2>";

$to = "atiera41001@gmail.com";
echo "<p style='color:#cbd5e1;'>Sending from: <b>".SMTP_USER."</b> via Port <b>".SMTP_PORT."</b></p>";
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
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Must be used for 587
    $mail->Port       = SMTP_PORT;
    $mail->Timeout    = 20;

    $mail->SMTPOptions = [
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
    ];

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($to, 'Admin Test');
    $mail->Subject = 'ATIERA App Password Test';
    $mail->Body    = 'Testing delivery via Google App Passwords on Port 587.';

    $mail->send();
    echo "</div>";
    echo "<h3 style='color:green; margin-top:20px;'>✅ GMAIL SMTP 587 WORKED!</h3>";
    echo "<p>Please check your inbox.</p>";
} catch (Exception $e) {
    echo "</div>";
    echo "<h3 style='color:red; margin-top:20px;'>❌ SMTP ERROR: " . $e->getMessage() . "</h3>";
    echo "<p style='color:#94a3b8'>If this says Connection Refused (111), Hostinger is entirely blocking outbound SMTP connections entirely.</p>";
}
echo "</div></body>";
?>
