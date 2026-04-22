<?php
// API Endpoint for Email Verification and Resending codes
// IT DOES NOT RENDER HTML. It returns JSON.

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../include/Config.php';

header('Content-Type: application/json');
session_start();

function json_out($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$userId = $_SESSION['temp_user_id'] ?? null;
$email = $_SESSION['temp_email'] ?? null;
$name = $_SESSION['temp_name'] ?? 'Admin';
$action = $_POST['action'] ?? 'verify';

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
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #d4af37; border-radius: 12px; background-color: #0b1538; padding: 30px; color: #ffffff;'>
                    <div style='text-align: center; padding-bottom: 20px;'>
                         <h2 style='color: #d4af37; margin: 0; font-size: 28px; letter-spacing: 1px;'>ATIERA</h2>
                         <p style='color: #94a3b8; font-size: 14px;'>Secure Dashboard Verification</p>
                    </div>
                    <div style='background-color: #15265e; border-radius: 10px; padding: 30px; text-align: center; border: 1px solid #2342a6;'>
                         <p style='font-size: 14px; color: #cbd5e1; margin-bottom: 15px;'>Your verification code is:</p>
                         <div style='font-size: 42px; font-weight: 800; color: #d4af37; letter-spacing: 12px; background: #0f1c49; border: 2px solid #d4af37; border-radius: 8px; display: inline-block; padding: 15px 35px; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.15);'>
                             " . $code . "
                         </div>
                         <p style='font-size: 12px; color: #f87171; margin-top: 25px;'>This code expires in 15 minutes.</p>
                    </div>
                    <div style='text-align: center; margin-top: 25px; color: #64748b; font-size: 12px;'>
                        If you did not request this, please secure your account immediately.
                    </div>
                </div>";
    
    // Calls the hardcoded function in Config.php
    return sendEmail($to, $name, $subject, $body);
}

try {
    $pdo = get_pdo();

    if ($action === 'resend') {
        $code = (string) random_int(100000, 999999);
        $expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO email_verifications (user_id, code, expires_at) VALUES (?,?,?)');
        $stmt->execute([$userId, $code, $expires]);

        $res = send_email($email, $name, $code);
        if ($res === true) {
            json_out(['ok' => true, 'message' => 'New code sent to ' . $email]);
        } else {
            json_out(['ok' => false, 'message' => 'Delivery Failed: ' . $res]);
        }
    }

    if ($action === 'verify') {
        $code = trim($_POST['code'] ?? '');
        $stmt = $pdo->prepare('SELECT code, expires_at FROM email_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 5');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        $valid = false;
        foreach ($rows as $row) {
            if ($row['code'] === $code && new DateTimeImmutable($row['expires_at']) > new DateTimeImmutable()) {
                $valid = true;
                break;
            }
        }
        if ($valid) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $_SESSION['temp_username'];
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $_SESSION['temp_role'] ?? 'staff';
            unset($_SESSION['temp_user_id']);
            $pdo->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$userId]);
            json_out(['ok' => true, 'redirect' => '../Modules/dashboard.php']);
        } else {
            json_out(['ok' => false, 'message' => 'Invalid or expired code.']);
        }
    }
} catch (Exception $e) {
    json_out(['ok' => false, 'message' => 'Server error.'], 500);
}
?>