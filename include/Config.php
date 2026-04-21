<?php
/**
 * ATIERA Hotel & Restaurant - Configuration File
 */

// --- 1. DATABASE CONFIGURATION ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'u240409748_atiera_db');
define('DB_USER', 'u240409748_atiera_admin');
define('DB_PASS', 'Atiera_Hotel_2024');

function get_pdo() {
    static $pdo;
    if (!$pdo) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed.");
        }
    }
    return $pdo;
}

// --- 2. EMAIL CONFIGURATION (PHPMailer Local Focus) ---
define('SMTP_HOST', 'localhost'); // Subukan nating gamitin ang local server
define('SMTP_USER', 'linbilcelestre31@gmail.com');
define('SMTP_PASS', 'poti vsjc wfth dzks');
define('SMTP_FROM_EMAIL', 'linbilcelestre31@gmail.com');
define('SMTP_FROM_NAME', 'ATIERA Hotel & Restaurant');

function sendEmail($to, $name, $subject, $body)
{
    $root = dirname(__DIR__); 
    require_once $root . '/PHPMailer/src/Exception.php';
    require_once $root . '/PHPMailer/src/PHPMailer.php';
    require_once $root . '/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // --- METHOD 1: Try Local SMTP (Bypasses external blocks) ---
        $mail->isSMTP();
        $mail->Host       = 'localhost'; // Usually unblocked for local mail
        $mail->SMTPAuth   = false;      // Local host often doesn't need auth
        $mail->Port       = 25;
        
        $mail->setFrom('admin@atierahotelandrestaurant.com', SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if ($mail->send()) return true;

    } catch (Exception $e1) {
        try {
            // --- METHOD 2: Try Gmail via Port 465 (PHPMailer) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = str_replace(' ', '', SMTP_PASS); 
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;
            $mail->Timeout    = 5;
            
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->send();
            return true;
        } catch (Exception $e2) {
            // --- METHOD 3: Native Mail Fallback (Via PHPMailer) ---
            try {
                $mail->isMail();
                $mail->setFrom('no-reply@atierahotelandrestaurant.com', SMTP_FROM_NAME);
                $mail->send();
                return true;
            } catch (Exception $e3) {
                return "PHPMailer Error: " . $mail->ErrorInfo;
            }
        }
    }
    return false;
}

// --- 3. BASE URL DETECTION ---
function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . "://" . $host . "/admin";
}