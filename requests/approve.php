<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$request = $stmt->fetch();

if (!$request) {
    $_SESSION['error'] = 'Заявка не найдена или уже обработана';
    header('Location: index.php');
    exit;
}

$pdo->beginTransaction();

try {
    // Обновляем статус заявки
    $stmt = $pdo->prepare("UPDATE requests SET status = 'approved', processed_by = ?, processed_at = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    // Создаём запись в выданном оборудовании
    $stmt = $pdo->prepare("INSERT INTO issued_equipment (request_id, equipment_name, equipment_type, quantity, issued_to_user_id, issued_by_admin_id, issued_at, expected_return_date) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->execute([
        $request['id'],
        $request['equipment_name'],
        $request['equipment_type'],
        $request['quantity'],
        $request['user_id'],
        $_SESSION['user_id'],
        $request['expected_return_date']
    ]);
    
    $pdo->commit();
    $_SESSION['success'] = 'Заявка одобрена';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Ошибка: ' . $e->getMessage();
}

header('Location: index.php');
exit;
?>