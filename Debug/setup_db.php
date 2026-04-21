<?php
require_once "../connections.php";

/**
 * DATABASE SETUP FOR SSO
 * This script ensures your Admin database has the correct secrets for HR3.
 */

$sql = "
CREATE TABLE IF NOT EXISTS department_secrets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department VARCHAR(50) UNIQUE,
  secret_key VARCHAR(255),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

$conn->query($sql);

// Generate a random high-entropy secret for this setup attempt
$secret = bin2hex(random_bytes(16));
$stmt = $conn->prepare("INSERT INTO department_secrets (department, secret_key) VALUES ('HR3', ?) ON DUPLICATE KEY UPDATE secret_key=VALUES(secret_key)");
$stmt->bind_param("s", $secret);

if ($stmt->execute()) {
    echo "<h3>SSO Setup Successful!</h3>";
    echo "Admin database is now using: <strong>$secret</strong> for HR3.<br>";
    echo "Make sure your HR3 database also has this exact same key in its <code>department_secrets</code> table.";
} else {
    echo "Error: " . $stmt->error;
}
?>