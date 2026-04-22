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
    $subject = "Verify your email";
    $body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Verify your email</h2>
                    <p>Hello $name,</p>
                    <p>Use the verification code below to sign in. It expires in 15 minutes.</p>
                    <div style='background: #1a233e; color: white; display: inline-block; padding: 12px 25px; font-size: 24px; font-weight: bold; border-radius: 6px; letter-spacing: 2px; margin: 20px 0;'>
                        $code
                    </div>
                    <p style='color: #666; font-size: 14px;'>If you didn't request this, you can ignore this email.</p>
                    <p style='color: #888;'>— ATIERA</p>
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
            json_out(['ok' => true, 'message' => 'Code sent! [DEV MODE CODE: ' . $code . ']']);
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