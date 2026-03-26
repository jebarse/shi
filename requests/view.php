<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT r.*, u.username, u.full_name, u.department FROM requests r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: index.php');
    exit;
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Заявка #<?= $request['id'] ?></h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Пользователь:</strong> <?= htmlspecialchars($request['username'] ?? $request['full_name']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Подразделение:</strong> <?= htmlspecialchars($request['department'] ?? '-') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Оборудование:</strong> <?= htmlspecialchars($request['equipment_name']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Количество:</strong> <?= $request['quantity'] ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Дата заявки:</strong> <?= date('d.m.Y H:i', strtotime($request['created_at'])) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Ожидаемый возврат:</strong> <?= $request['expected_return_date'] ? date('d.m.Y', strtotime($request['expected_return_date'])) : '-' ?>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Причина:</strong><br>
                    <?= nl2br(htmlspecialchars($request['reason'])) ?>
                </div>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-secondary">Назад</a>
                    <?php if ($request['status'] == 'pending'): ?>
                        <a href="approve.php?id=<?= $request['id'] ?>" class="btn btn-success">Одобрить</a>
                        <a href="reject.php?id=<?= $request['id'] ?>" class="btn btn-danger">Отклонить</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>