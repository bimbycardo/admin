<?php
// API Endpoint for Email Verification and Resending codes
// IT DOES NOT RENDER HTML. It returns JSON.

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../include/Config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
session_start();

function json_out($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// 1. Context variables
$userId = $_SESSION['temp_user_id'] ?? null;
$email = $_SESSION['temp_email'] ?? null;
$name = $_SESSION['temp_name'] ?? 'Admin';

$action = $_POST['action'] ?? 'verify';

// Validate session only for actions that depend on it
if ($action === 'verify' || $action === 'resend') {
    if (empty($userId) || empty($email)) {
        json_out(['ok' => false, 'message' => 'Session expired. Please login again.'], 401);
    }
}

// --- HELPER: Send Email ---
function send_email($to, $name, $code)
{
    $subject = '🔐 ATIERA Verification Code';
    $body = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e1e1e1; border-radius: 12px; background-color: #ffffff;'>
                <div style='text-align: center; margin-bottom: 25px;'>
                    <h2 style='color: #1b2f73; margin: 0;'>Verification Required</h2>
                    <p style='color: #64748b;'>Secure Login Access</p>
                </div>
                <div style='padding: 30px; background-color: #f8fafc; border-radius: 10px; text-align: center;'>
                    <p style='font-size: 14px; color: #334155; margin-bottom: 20px;'>Hello <strong>$name</strong>, use the code below to verify your account:</p>
                    <div style='font-size: 36px; font-weight: 800; letter-spacing: 12px; color: #1b2f73; background: #fff; padding: 15px; border: 2px solid #d4af37; border-radius: 8px; display: inline-block;'>
                        $code
                    </div>
                    <p style='font-size: 12px; color: #94a3b8; margin-top: 20px;'>This code will expire in 15 minutes.</p>
                </div>
                <p style='margin-top: 25px; font-size: 13px; color: #64748b; line-height: 1.6; text-align: center;'>
                    If you didn't request this code, please ignore this email or contact support.
                </p>
                <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                <p style='font-size: 11px; color: #94a3b8; text-align: center;'>
                    &copy; " . date('Y') . " ATIERA Hotel Management System.
                </p>
            </div>
        ";
    return sendEmail($to, $name, $subject, $body);
}

try {
    $pdo = get_pdo();

    // --- ACTION: RESEND ---
    if ($action === 'resend') {
        // Generate new code
        $code = (string) random_int(100000, 999999);
        $expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare('INSERT INTO email_verifications (user_id, code, expires_at) VALUES (?,?,?)');
        $stmt->execute([$userId, $code, $expires]);

        $sendResult = send_email($email, $name, $code);
        if ($sendResult === true) {
            json_out(['ok' => true, 'message' => 'New code sent to ' . $email]);
        } else {
            json_out(['ok' => false, 'message' => 'Email Error: ' . $sendResult], 500);
        }
    }

    // --- ACTION: COMPLETE REGISTRATION (Set first password) ---
    if ($action === 'complete_registration') {
        $code = trim($_POST['code'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $newPass = $_POST['new_password'] ?? '';

        if (empty($newPass) || strlen($newPass) < 6) {
            json_out(['ok' => false, 'message' => 'Password too short.'], 400);
        }

        // Find user by code and email (using JOIN to be safe)
        $stmt = $pdo->prepare('
            SELECT u.id, u.username, u.full_name, ev.expires_at 
            FROM email_verifications ev 
            JOIN users u ON ev.user_id = u.id 
            WHERE ev.code = ? AND u.email = ?
            ORDER BY ev.id DESC LIMIT 1
        ');
        $stmt->execute([$code, $email]);
        $row = $stmt->fetch();

        if ($row) {
            $exp = new DateTimeImmutable($row['expires_at']);
            if ($exp > new DateTimeImmutable()) {
                // SUCCESS: Set password and login
                $pdo->beginTransaction();

                // Update password
                $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $stmt->execute([password_hash($newPass, PASSWORD_DEFAULT), $row['id']]);

                // Set session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $row['full_name'];

                // Cleanup codes
                $pdo->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$row['id']]);

                $pdo->commit();
                json_out(['ok' => true, 'message' => 'Registration complete!', 'redirect' => '../Modules/dashboard.php']);
            } else {
                json_out(['ok' => false, 'message' => 'Code expired.'], 400);
            }
        } else {
            json_out(['ok' => false, 'message' => 'Invalid code or email.'], 400);
        }
    }

    // --- ACTION: VERIFY (Regular login) ---
    if ($action === 'verify') {
        $code = trim($_POST['code'] ?? '');
        if (!preg_match('/^\d{6}$/', $code)) {
            json_out(['ok' => false, 'message' => 'Invalid code format.'], 400);
        }

        // Check DB
        $stmt = $pdo->prepare('SELECT code, expires_at FROM email_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 5');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $valid = false;
        $now = new DateTimeImmutable();

        foreach ($rows as $row) {
            if (hash_equals($row['code'], $code)) {
                $exp = new DateTimeImmutable($row['expires_at']);
                if ($exp > $now) {
                    $valid = true;
                    break;
                }
            }
        }

        if ($valid) {
            // Success! Promote temp session to real session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $_SESSION['temp_username'];
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            // Note: Role removed as per previous instructions

            // Cleanup
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_username']);
            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_name']);
            // unset($_SESSION['temp_role']); // Was removed previously

            $pdo->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$userId]);

            json_out(['ok' => true, 'redirect' => '../Modules/dashboard.php']);
        } else {
            json_out(['ok' => false, 'message' => 'Invalid or expired code.'], 400);
        }
    }

} catch (Exception $e) {
    json_out(['ok' => false, 'message' => 'Server error.'], 500);
}
?>