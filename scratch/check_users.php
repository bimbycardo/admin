<?php
require_once 'db/db.php';
$pdo = get_pdo();
$stmt = $pdo->query("DESCRIBE users");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
