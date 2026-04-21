<?php
/**
 * ATIERA — ULTIMATE SMTP POWER DEBUGGER
 * Run this directly in your browser: [yourdomain]/admin/test_phpmailer.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/include/Config.php';

echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Atiera Mail Diagnostic</title>";
echo "<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #f1f5f9; padding: 40px; line-height:1.6; }
    .container { max-width: 1000px; margin: 0 auto; background: #1e293b; padding: 30px; border-radius: 16px; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
    h1 { color: #d4af37; border-bottom: 2px solid #334155; padding-bottom: 15px; font-size: 24px; }
    .section { margin-bottom: 30px; padding: 20px; background: #020617; border-radius: 12px; border-left: 5px solid #d4af37; }
    .success { color: #4ade80; font-weight: bold; }
    .fail { color: #f87171; font-weight: bold; }
    .log { background: #000; color: #22c55e; padding: 15px; border-radius: 8px; font-family: 'Consolas', monospace; font-size: 13px; overflow-x: auto; white-space: pre-wrap; margin-top:10px; }
    .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; text-transform: uppercase; margin-right: 5px; }
    .badge-open { background: #166534; color: #bbf7d0; }
    .badge-closed { background: #991b1b; color: #fecdd3; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔍 ATIERA Deep Mail Recovery Tool</h1>";

// 1. Check Configuration Constants
echo "<div class='section'><h3>1. Configuration Check</h3>";
$constants = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS'];
foreach ($constants as $c) {
    if (defined($c)) {
        echo "✅ $c: <span class='success'>Defined</span> (" . ( ($c == 'SMTP_PASS') ? '****' : constant($c) ) . ")<br>";
    } else {
        echo "❌ $c: <span class='fail'>NOT DEFINED</span><br>";
    }
}
echo "</div>";

// 2. Port Connectivity Check
echo "<div class='section'><h3>2. Hostinger Port Scan (Outgoing)</h3>";
$ports = [465, 587, 25];
foreach ($ports as $p) {
    echo "Port $p: ";
    $fp = @fsockopen('smtp.gmail.com', $p, $errno, $errstr, 5);
    if ($fp) {
        echo "<span class='badge badge-open'>Open</span><br>";
        fclose($fp);
    } else {
        echo "<span class='badge badge-closed'>Closed</span> (Timeout/Block)<br>";
    }
}
echo "</div>";

// 3. Execution Test
echo "<div class='section'><h3>3. PHPMailer Live Handshake</h3>";
$to = "atiera41001@gmail.com";
$name = "Atiera Admin";

$root = __DIR__;
$paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
$src = '';
foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }

if (!$src) {
    die("<span class='fail'>CRITICAL: PHPMailer folder not found. Please upload PHPMailer folder.</span>");
}

require_once $src . 'Exception.php';
require_once $src . 'PHPMailer.php';
require_once $src . 'SMTP.php';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);
$log = "";

try {
    echo "Starting SMTP Handshake with Gmail...<br>";
    $mail->isSMTP();
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = function($str, $level) use (&$log) { $log .= $str; };
    
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = (SMTP_PORT == 465) ? 'ssl' : 'tls';
    $mail->Port       = SMTP_PORT;
    $mail->Timeout    = 20;
    
    $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

    $mail->setFrom(SMTP_USER, 'ATIERA Test');
    $mail->addAddress($to, $name);
    $mail->Subject = 'ATIERA Connection Test';
    $mail->Body    = 'Test successful at ' . date('Y-m-d H:i:s');

    $mail->send();
    echo "<div class='log'>$log</div>";
    echo "<p class='success'>✅ SUCCESS! Check your inbox/spam.</p>";
} catch (Exception $e) {
    echo "<div class='log'>$log</div>";
    echo "<p class='fail'>❌ PHPMailer Failed: " . $e->getMessage() . "</p>";
    
    echo "<hr><h3>4. Native Mail Fallback Test</h3>";
    $official = 'admin@atierahotelandrestaurant.com';
    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: <$official>\r\n";
    if (@mail($to, "Fallback Test", "Native mail test", $headers, "-f$official")) {
        echo "<p class='success'>✅ Native mail() reported success. Check SPAM folder.</p>";
    } else {
        echo "<p class='fail'>❌ Native mail() also failed. Hostinger is completely blocking outgoing mail.</p>";
    }
}

echo "</div></div></body></html>";
?>
