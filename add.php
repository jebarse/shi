<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $inventory_number = trim($_POST['inventory_number'] ?? '');
    $status = $_POST['status'] ?? 'available';
    $location = trim($_POST['location'] ?? '');
    $purchase_date = $_POST['purchase_date'] ?: null;
    $warranty_until = $_POST['warranty_until'] ?: null;
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($type)) {
        $error = 'Название и тип оборудования обязательны';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO equipment (name, type, serial_number, inventory_number, status, location, purchase_date, warranty_until, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $type, $serial_number, $inventory_number, $status, $location, $purchase_date, $warranty_until, $notes, $_SESSION['user_id']]);
            $success = 'Оборудование успешно добавлено';
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении: ' . $e->getMessage();
        }
    }
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>Добавление оборудования</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Тип *</label>
                        <input type="text" name="type" class="form-control" required value="<?= htmlspecialchars($_POST['type'] ?? '') ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Серийный номер</label>
                        <input type="text" name="serial_number" class="form-control" value="<?= htmlspecialchars($_POST['serial_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Инвентарный номер</label>
                        <input type="text" name="inventory_number" class="form-control" value="<?= htmlspecialchars($_POST['inventory_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="available">В наличии</option>
                            <option value="issued">Выдано</option>
                            <option value="repair">В ремонте</option>
                            <option value="broken">Сломано</option>
                            <option value="written_off">Списано</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Местоположение</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Дата покупки</label>
                        <input type="date" name="purchase_date" class="form-control" value="<?= htmlspecialchars($_POST['purchase_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Гарантия до</label>
                        <input type="date" name="warranty_until" class="form-control" value="<?= htmlspecialchars($_POST['warranty_until'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Примечания</label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Назад</a>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>