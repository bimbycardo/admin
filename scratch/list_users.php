<?php
require_once __DIR__ . '/../db/db.php';
$pdo = get_pdo();
$stmt = $pdo->query("SELECT id, username, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "ID | Username | Email | Role\n";
echo "---|----------|-------|-----\n";
foreach ($users as $user) {
    echo $user['id'] . " | " . $user['username'] . " | " . $user['email'] . " | " . $user['role'] . "\n";
}
