<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;

if ($id) {
    // Проверяем, не выдано ли оборудование
    $check = $pdo->prepare("SELECT id FROM issued_equipment WHERE equipment_id = ? AND actual_return_date IS NULL");
    $check->execute([$id]);
    if ($check->fetch()) {
        $_SESSION['error'] = 'Нельзя удалить оборудование, которое currently выдано';
    } else {
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header('Location: index.php');
exit;
?>