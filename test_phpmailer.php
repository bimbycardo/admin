<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHPMailer Deep Test (Corrected)</h2>";

$to = "atiera41001@gmail.com";
$name = "Test User";
$subject = "PHPMailer Debug Test";
$body = "<h1>It Works!</h1><p>This is a test email.</p>";

$root = __DIR__; 
@include_once $root . '/PHPMailer/src/Exception.php';
@include_once $root . '/PHPMailer/src/PHPMailer.php';
@include_once $root . '/PHPMailer/src/SMTP.php';

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("PHPMailer Not Found.");
}

// ATTEMPT 1: SMTP
try {
    echo "<h3>Attempting SMTP (Gmail Port 465)...</h3><pre>";
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = 'echo';
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'linbilcelestre31@gmail.com';
    $mail->Password   = 'potivsjcwfthdzks';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    
    $mail->setFrom('linbilcelestre31@gmail.com', 'ATIERA Hotel');
    $mail->addAddress($to, $name);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    
    $mail->send();
    echo "</pre><h4 style='color:green'>SUCCESS: SMTP Worked!</h4>";
} catch (Exception $e) {
    echo "</pre><h4 style='color:red'>SMTP FAILED.</h4>";
    
    // ATTEMPT 2: NATIVE MAIL
    try {
        echo "<h3>Attempting Fallback (isMail)...</h3><pre>";
        $mail2 = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail2->SMTPDebug = 3;
        $mail2->Debugoutput = 'echo';
        $mail2->isMail();
        $mail2->setFrom('admin@atierahotelandrestaurant.com', 'ATIERA Hotel');
        $mail2->addAddress($to, $name);
        $mail2->isHTML(true);
        $mail2->Subject = "Fallback Test";
        $mail2->Body    = "Fallback message";
        $mail2->send();
        echo "</pre><h4 style='color:blue'>SUCCESS: isMail() worked!</h4>";
    } catch (Exception $e2) {
        echo "</pre><h4 style='color:red'>ALL METHODS FAILED.</h4>";
    }
}
