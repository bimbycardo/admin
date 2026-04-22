<?php
require_once 'db/db.php';
$pdo = get_pdo();
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $tables);
?>
