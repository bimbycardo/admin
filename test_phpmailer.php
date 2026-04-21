<?php
/**
 * PHPMailer ULTIMATE DEBUGGER (Secure Version)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load centralized config
require_once __DIR__ . '/include/Config.php';

echo "<body style='background:#f1f5f9; font-family:sans-serif; padding:20px;'>";
echo "<div style='max-width:900px; margin:0 auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); border:1px solid #e2e8f0;'>";
echo "<h2 style='color:#1e293b; border-bottom:2px solid #d4af37; padding-bottom:10px;'>🔍 PHPMailer Deep Diagnostic (Secure)</h2>";

// Check if constants are available
if (!defined('SMTP_USER')) {
    die("<p style='color:red;'>❌ ERROR: Config settings not found. Please ensure include/Config.php is correct.</p>");
}

$to = "atiera41001@gmail.com";
$name = "Diagnostic Recipient";
$subject = "🧪 Testing ATIERA Email Engine";
$body = "<h1>Connection Test Successful!</h1><p>If you see this, your server can send emails.</p>";

$root = __DIR__; 
require_once $root . '/PHPMailer/src/Exception.php';
require_once $root . '/PHPMailer/src/PHPMailer.php';
require_once $root . '/PHPMailer/src/SMTP.php';

// ---------------------------------------------------------
// ATTEMPT 1: GMAIL SMTP
// ---------------------------------------------------------
echo "<div style='margin-bottom:30px;'>";
echo "<h3 style='color:#1d4ed8;'>Step 1: Attempting Gmail SMTP (From Config)</h3>";
echo "<div style='background:#0f172a; color:#cbd5e1; padding:15px; border-radius:8px; overflow-x:auto; font-size:12px;'><pre>";

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = 'echo';
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = (SMTP_PORT == 465) ? 'ssl' : 'tls';
    $mail->Port       = SMTP_PORT;
    $mail->Timeout    = 10;
    
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($to, $name);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    
    $mail->SMTPOptions = [
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
    ];

    $mail->send();
    echo "</pre></div>";
    echo "<h4 style='color:green;'>✅ SUCCESS: SMTP Method Worked!</h4>";
} catch (Exception $e) {
    echo "</pre></div>";
    echo "<h4 style='color:red;'>❌ SMTP FAILED: " . $mail->ErrorInfo . "</h4>";
    
    // ---------------------------------------------------------
    // ATTEMPT 2: NATIVE PHP MAIL FAILBACK (Custom Headers)
    // ---------------------------------------------------------
    echo "<hr style='border:1px dashed #e2e8f0; margin:30px 0;'>";
    echo "<h3 style='color:#1d4ed8;'>Step 2: Attempting Native Fallback (mail)</h3>";
    echo "<p style='color:#64748b; font-size:13px;'>Testing direct server dispatch...</p>";
    
    $officialEmail = 'admin@atierahotelandrestaurant.com';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: ATIERA Hotel <$officialEmail>\r\n";
    $headers .= "Reply-To: $officialEmail\r\n";
    
    if (@mail($to, "Fallback: " . $subject, $body, $headers, "-f$officialEmail")) {
        echo "<h4 style='color:blue;'>✅ SUCCESS: Native mail() worked!</h4>";
    } else {
        echo "<h4 style='color:red; font-size:18px;'>🚨 ALL METHODS FAILED.</h4>";
        echo "<p style='color:#ef4444;'>Final Diagnosis: Host provider blockade detected.</p>";
    }
}
echo "</div>";
echo "</div>";
?>
