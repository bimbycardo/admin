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
            <div style=\"font-family:Arial,sans-serif; max-width:600px; margin:0 auto; border:1px solid #e1e1e1; border-radius:12px; background-color:#ffffff; padding:20px;\">
                <div style=\"text-align:center; padding-bottom:20px;\">
                     <h2 style=\"color:#1b2f73; margin:0;\">Email Verification</h2>
                     <p style=\"color:#64748b; font-size:14px;\">Use the new code below to complete your login.</p>
                </div>
                <div style=\"background-color:#f8fafc; border-radius:10px; padding:30px; text-align:center; border:1px solid #f1f5f9;\">
                     <p style=\"font-size:14px; color:#334155; margin-bottom:10px;\">Your new verification code:</p>
                     <div style=\"font-size:42px; font-weight:800; color:#d4af37; letter-spacing:10px; background:#fff; border:2px solid #d4af37; border-radius:8px; display:inline-block; padding:10px 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);\">
                         " . $code . "
                     </div>
                     <p style=\"font-size:12px; color:#b91c1c; margin-top:20px;\">This code will expire in 15 minutes.</p>
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

        $res = sendEmail($email, $name, "Verification Code: $code", "<h2>Code: $code</h2>");
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