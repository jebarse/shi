<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;

if ($id && $id != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Пользователь удалён';
}

header('Location: index.php');
exit;
?>