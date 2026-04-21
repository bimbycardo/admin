<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (PURE NATIVE MODE)
 * PROOF: All SMTP ports (465, 587, 25) are CLOSED on this server.
 * Only native mail() reports success.
 */

if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'noreply@atierahotelandrestaurant.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ATIERA Security');

/**
 * Send email using Native PHP Mail (The only working method on this host)
 */
function sendEmail($to, $name, $subject, $body)
{
    // Use the official domain-based email to survive spam filters
    $from = SMTP_FROM_EMAIL;
    $fromName = SMTP_FROM_NAME;
    
    // Fortified Headers for Gmail Delivery
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $fromName <$from>" . "\r\n";
    $headers .= "Reply-To: $from" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 1 (Highest)" . "\r\n";
    $headers .= "Importance: High" . "\r\n";

    // Attempt to send
    if (@mail($to, $subject, $body, $headers, "-f$from")) {
        return true;
    }

    return "Server Block: Native mail() returned false. Please contact Hostinger Support.";
}

function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}
?>