<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$equipment_id = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : 0;
$preset_equipment = null;

if ($equipment_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ? AND status = 'available'");
    $stmt->execute([$equipment_id]);
    $preset_equipment = $stmt->fetch();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $equipment_type = trim($_POST['equipment_type'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $expected_return = $_POST['expected_return'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $equipment_id_post = !empty($_POST['equipment_id']) ? (int)$_POST['equipment_id'] : null;

    if (empty($equipment_name) || empty($reason)) {
        $error = 'Название оборудования и причина обязательны';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO requests (user_id, equipment_id, equipment_name, equipment_type, quantity, reason, expected_return_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $equipment_id_post, $equipment_name, $equipment_type, $quantity, $reason, $expected_return ?: null]);
            $success = 'Заявка успешно отправлена! Ожидайте решения администратора.';
        } catch (PDOException $e) {
            $error = 'Ошибка при создании заявки: ' . $e->getMessage();
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>Создание заявки на оборудование</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <a href="my-requests.php" class="btn btn-primary">Перейти к моим заявкам</a>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="post" class="card">
            <div class="card-body">
                <?php if ($preset_equipment): ?>
                    <input type="hidden" name="equipment_id" value="<?= $preset_equipment['id'] ?>">
                    <div class="alert alert-info">
                        Вы запрашиваете: <strong><?= htmlspecialchars($preset_equipment['name']) ?></strong>
                        (тип: <?= htmlspecialchars($preset_equipment['type']) ?>)
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Название оборудования *</label>
                    <input type="text" name="equipment_name" class="form-control" required
                           value="<?= htmlspecialchars($preset_equipment['name'] ?? $_POST['equipment_name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Тип оборудования</label>
                    <input type="text" name="equipment_type" class="form-control"
                           value="<?= htmlspecialchars($preset_equipment['type'] ?? $_POST['equipment_type'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Количество</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="<?= (int)($_POST['quantity'] ?? 1) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Причина / Обоснование *</label>
                    <textarea name="reason" class="form-control" rows="4" required><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                    <small class="text-muted">Опишите, для каких задач нужно оборудование</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ожидаемая дата возврата</label>
                    <input type="date" name="expected_return" class="form-control"
                           value="<?= htmlspecialchars($_POST['expected_return'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-primary">Отправить заявку</button>
                <a href="equipment.php" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>