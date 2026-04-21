<?php
/**
 * ATIERA — ULTIMATE NATIVE MAIL TESTER
 * Run this directly in your browser
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/include/Config.php';

echo "<body style='background:#f1f5f9; font-family:sans-serif; padding:20px;'>";
echo "<div style='max-width:800px; margin:auto; background:white; padding:30px; border-radius:10px; border:1px solid #cbd5e1;'>";
echo "<h2 style='color:#1e293b;'>🔍 ATIERA Deep Mail Recovery Tool (Native Mode)</h2>";

$to = "atiera41001@gmail.com";
echo "<p>Preparing to dispatch test email to: <b>$to</b></p>";
echo "<div style='background:#0f172a; color:#22c55e; padding:15px; border-radius:8px; font-family:monospace; font-size:13px; white-space:pre-wrap;'>";

$root = __DIR__;
$paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
$src = '';
foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }

if(!$src) {
    die("❌ PHPMailer files not found.");
}

require_once $src . 'Exception.php';
require_once $src . 'PHPMailer.php';
require_once $src . 'SMTP.php'; // Required by PHPMailer core even if using isMail()

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    // Enable verbose output
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = 'echo';
    
    // WE ARE USING THE NEW "NATIVE MAIL" MODE
    $mail->isMail();
    
    // Pull the authorized sender from the new Config.php
    $mail->setFrom(OFFICIAL_SENDER, OFFICIAL_NAME);
    $mail->Sender = OFFICIAL_SENDER; // This is the secret sauce for Hostinger!
    
    $mail->addAddress($to, 'Admin');
    $mail->Subject = 'ATIERA Connection Test (Native Mail)';
    $mail->Body    = '<h2>Test successful!</h2><p>Your server perfectly dispatched this email using Native Mail formatting.</p><hr><p>Time: ' . date('Y-m-d H:i:s') . '</p>';
    
    $mail->send();
    echo "</div>";
    echo "<h3 style='color:green; margin-top:20px;'>✅ NATIVE PHP MAIL DISPATCHED SUCCESSFULLY!</h3>";
    echo "<p>Your server accepted the email. Please check the <b>Inbox</b> and <b>Spam folder</b> of $to.</p>";
} catch (Exception $e) {
    echo "</div>";
    echo "<h3 style='color:red; margin-top:20px;'>❌ DISPATCH FAILED: " . $e->getMessage() . "</h3>";
}
echo "</div></body>";
?>
