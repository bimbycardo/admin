<?php
/**
 * ATIERA Hotel & Restaurant - Email Configuration (SOCKET BINDTO FIX)
 * Uses the advanced bindto socket parameter to force IPv4 WITHOUT changing the hostname.
 */

// User's requested Gmail configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'atiera41001@gmail.com');
define('SMTP_PASS', 'dxis mokl icnb iemt');

function sendEmail($to, $name, $subject, $body)
{
    // Native PHP Mail Implementation
    $domainSender = 'atiera41001@gmail.com';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: ATIERA Security <$domainSender>\r\n";
    $headers .= "Reply-To: $domainSender\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    // Call native mail function
    if (@mail($to, $subject, $body, $headers, "-f$domainSender")) {
        return true;
    }

    return "Native PHP mail() failed to send the email.";
}

function getBaseUrl()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
}
?>