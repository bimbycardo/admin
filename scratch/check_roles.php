<?php
require_once __DIR__ . '/../db/db.php';
$pdo = get_pdo();
$stmt = $pdo->query("SELECT email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "Email: " . $user['email'] . " | Role: " . $user['role'] . "\n";
}
