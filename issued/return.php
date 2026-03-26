<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $condition = trim($_POST['return_condition'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    $stmt = $pdo->prepare("UPDATE issued_equipment SET actual_return_date = NOW(), return_condition = ?, notes = ? WHERE id = ? AND actual_return_date IS NULL");
    $stmt->execute([$condition, $notes, $id]);
    
    $_SESSION['success'] = 'Возврат отмечен';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT ie.*, u.username as user_name FROM issued_equipment ie JOIN users u ON ie.issued_to_user_id = u.id WHERE ie.id = ? AND ie.actual_return_date IS NULL");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: index.php');
    exit;
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Отметка возврата</h3>
            </div>
            <div class="card-body">
                <p><strong>Оборудование:</strong> <?= htmlspecialchars($item['equipment_name']) ?></p>
                <p><strong>Пользователь:</strong> <?= htmlspecialchars($item['user_name']) ?></p>
                <p><strong>Дата выдачи:</strong> <?= date('d.m.Y', strtotime($item['issued_at'])) ?></p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Состояние при возврате</label>
                        <select name="return_condition" class="form-select" required>
                            <option value="">Выберите состояние</option>
                            <option value="good">Хорошее</option>
                            <option value="damaged">С повреждениями</option>
                            <option value="repair">Требует ремонта</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Примечания</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Подтвердить возврат</button>
                    <a href="index.php" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>