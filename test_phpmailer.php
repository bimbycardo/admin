<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/include/Config.php';

echo "<body style='background:#f1f5f9; font-family:sans-serif; padding:20px;'>";
echo "<h2>🔍 ATIERA Deep Mail Recovery Tool (IPv4 Force Fix)</h2>";

$to = "atiera41001@gmail.com";
$root = __DIR__;
$paths = [$root.'/PHPMailer/src/', $root.'/phpmailer/src/'];
$src = '';
foreach($paths as $p) if(file_exists($p.'PHPMailer.php')) { $src = $p; break; }

require_once $src . 'Exception.php';
require_once $src . 'PHPMailer.php';
require_once $src . 'SMTP.php';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    echo "<b>Resolving Host: </b>" . gethostbyname('smtp.gmail.com') . "<br>";
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = 'echo';
    $mail->isSMTP();
    $mail->Host       = gethostbyname('smtp.gmail.com'); // IPv4 FORCE
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
    $mail->setFrom(SMTP_USER, 'ATIERA Test');
    $mail->addAddress($to, 'Admin');
    $mail->Subject = 'ATIERA Connection Test';
    $mail->Body    = 'Test successful!';
    $mail->send();
    echo "<h3 style='color:green'>✅ SUCCESS OVER IPv4!</h3>";
} catch (Exception $e) {
    echo "<h3 style='color:red'>❌ FAILED: " . $e->getMessage() . "</h3>";
}
?>
