<?php
/**
 * ATIERA — THE FAILOVER DIAGNOSTIC
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/include/Config.php';

echo "<body style='background:#0f172a; font-family:sans-serif; padding:40px;'>";
echo "<div style='max-width:800px; margin:auto; background:#1e293b; padding:30px; border-radius:10px; border:1px solid #334155;'>";
echo "<h2 style='color:#d4af37;'>🔍 Dual Delivery Test (App Password -> Hostinger Gateway)</h2>";

$to = "atiera41001@gmail.com";
echo "<div style='background:#020617; color:#cbd5e1; padding:20px; border-radius:8px; font-family:monospace; font-size:14px; white-space:pre-wrap; border-left:4px solid #38bdf8;'>";

echo "Initiating sendEmail() simulator...\n";
$result = sendEmail($to, "Admin Test", "ATIERA Failover Test", "Testing dual delivery matrix.");

echo "</div>";

if ($result === true) {
    echo "<h3 style='color:green; margin-top:20px;'>✅ EMAIL DISPATCHED!</h3>";
    echo "<p style='color:#94a3b8;'>If you do not see it in Inbox, check Spam. Hostinger's Firewall 111 was bypassed successfully.</p>";
} else {
    echo "<h3 style='color:red; margin-top:20px;'>❌ FATAL ERROR:</h3>";
    echo "<p style='color:#f87171;'>" . htmlspecialchars($result) . "</p>";
}
echo "</div></body>";
?>
