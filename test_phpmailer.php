<?php
/**
 * ATIERA — LIVE BACKEND ERROR VIEWER
 * This tool reads exactly what happens when you click "Resend Code"
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<body style='background:#0f172a; font-family:sans-serif; padding:40px;'>";
echo "<div style='max-width:800px; margin:auto; background:#1e293b; padding:30px; border-radius:10px; border:1px solid #334155;'>";
echo "<h2 style='color:#d4af37;'>🔍 Live Backend Email Logs</h2>";
echo "<p style='color:#cbd5e1;'>Refresh this page after clicking <b>Resend Code</b> to see backend errors.</p>";

$logFile = __DIR__ . '/em_log.txt';

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    if (empty($content)) {
        echo "<div style='background:#020617; color:#94a3b8; padding:20px; border-radius:8px; font-family:monospace;'>No logs yet. Click Resend Code first.</div>";
    } else {
        echo "<div style='background:#000; color:#38bdf8; padding:20px; border-radius:8px; font-family:monospace; font-size:14px; white-space:pre-wrap; border-left:4px solid #38bdf8;'>";
        echo htmlspecialchars($content);
        echo "</div>";
    }
    
    // Clear logs button
    if (isset($_GET['clear'])) {
        file_put_contents($logFile, "");
        echo "<script>window.location='test_phpmailer.php';</script>";
    }
    echo "<br><a href='test_phpmailer.php?clear=1' style='color:#ef4444; text-decoration:none; font-size:14px;'>🗑️ Clear Logs</a>";

} else {
    echo "<div style='background:#020617; color:#94a3b8; padding:20px; border-radius:8px; font-family:monospace;'>Log file does not exist yet. Please test the login/resend first.</div>";
}

echo "</div></body>";
?>
