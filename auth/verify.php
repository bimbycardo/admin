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

        if (send_email($email, $name, $code) === true) {
            json_out(['ok' => true, 'message' => 'New code sent to ' . $email]);
        } else {
            json_out(['ok' => false, 'message' => 'Failed to send email. Please check your network.']);
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