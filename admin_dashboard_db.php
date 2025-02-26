<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'members';

$queryMap = [
    'members' => "SELECT id, username, email FROM users",
    'products' => "SELECT id, name, price, stock_quantity FROM products",
    'news' => "SELECT id, title, created_at FROM news",
    'orders' => "SELECT id, user_id, total_price, status FROM orders",
    'payments' => "SELECT id, order_id, amount, status FROM payments",
    'promotions' => "SELECT id, title, discount_percentage FROM promotions"
];

$sql = $queryMap[$page];
$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tableData = "<table class='table table-striped'><tr>";
foreach (array_keys($rows[0]) as $col) {
    $tableData .= "<th>{$col}</th>";
}
$tableData .= "</tr>";

foreach ($rows as $row) {
    $tableData .= "<tr>";
    foreach ($row as $cell) {
        $tableData .= "<td>{$cell}</td>";
    }
    $tableData .= "</tr>";
}
$tableData .= "</table>";
?>
