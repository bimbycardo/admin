<?php
require_once __DIR__ . '/../include/Config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['userId'] ?? '';
$fullName = $input['fullName'] ?? '';
$email = $input['email'] ?? '';
$recoveryCode = $input['recoveryCode'] ?? '';
$recoveryPassword = $input['recoveryPassword'] ?? '';

if (empty($email) || empty($fullName)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$subject = "Account Recovery Notice - ATIERA Admin Panel";
$body = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <img src='https://atierahotelandrestaurant.com/assets/img/logo.png' alt='Atiéra Logo' style='height: 80px;'>
    </div>
    <h2 style='color: #1e293b; text-align: center;'>Account Recovery Generated</h2>
    <p style='color: #475569;'>Hello <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
    <p style='color: #475569;'>An administrator has generated account recovery credentials for your account. Please use the following details to regain access:</p>
    
    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin: 20px 0;'>
        <div style='margin-bottom: 10px;'>
            <span style='color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 700;'>Temporary Password:</span><br>
            <span style='font-family: monospace; font-size: 1.2rem; color: #1e293b; font-weight: 700;'>" . htmlspecialchars($recoveryPassword) . "</span>
        </div>
        <div>
            <span style='color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 700;'>Verification Code:</span><br>
            <span style='font-family: monospace; font-size: 1.2rem; color: #3b82f6; font-weight: 700;'>" . htmlspecialchars($recoveryCode) . "</span>
        </div>
    </div>
    
    <p style='color: #64748b; font-size: 0.85rem;'>Note: This password is temporary. You will be prompted to change it upon successful login.</p>
    <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;'>
    <p style='color: #94a3b8; font-size: 0.75rem; text-align: center;'>&copy; " . date('Y') . " Atiéra Hotel & Restaurant. High Security Admin Environment.</p>
</div>
";

$result = sendEmail($email, $fullName, $subject, $body);

if ($result === true || (is_string($result) && strpos($result, 'Native Mail sent') !== false)) {
    echo json_encode(['success' => true, 'message' => 'Recovery email sent successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Email failed: ' . $result]);
}
