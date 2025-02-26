<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'members';

$queryMap = [
    'members' => "SELECT COUNT(*) as total, role FROM users GROUP BY role",
    'products' => "SELECT name, stock_quantity FROM products",
    'news' => "SELECT title, COUNT(id) as views FROM news GROUP BY title",
    'orders' => "SELECT status, COUNT(*) as total FROM orders GROUP BY status",
    'payments' => "SELECT status, COUNT(*) as total FROM payments GROUP BY status",
    'promotions' => "SELECT title, discount_percentage FROM promotions"
];

$sql = $queryMap[$page];
$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];

foreach ($rows as $row) {
    // Use first column as label, second column as value
    $labels[] = array_values($row)[0];
    $values[] = array_values($row)[1];
}

echo json_encode(['labels' => $labels, 'values' => $values]);
?>
