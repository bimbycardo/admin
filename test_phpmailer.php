<?php
/**
 * PHPMailer Diagnostic Test
 * This file is for internal testing only.
 */

// Load centralized config
require_once __DIR__ . '/include/Config.php';

// Check if constants are available
if (!defined('SMTP_USER') || !defined('SMTP_PASS')) {
    die("Error: Config settings not found.");
}

$to = SMTP_USER; // Send test to yourself
$name = "Diagnostic Test";

// USE THE CENTRAL ENGINE
$result = sendEmail($to, $name, "🔧 SMTP Connection Test", "This is a diagnostic message to verify your SMTP settings.");

if ($result === true) {
    echo "<h2 style='color:green;'>SUCCESS: Email sent successfully!</h2>";
} else {
    echo "<h2 style='color:red;'>FAILED: Email could not be sent.</h2>";
    echo "Error Detail: " . htmlspecialchars($result);
}
?>
