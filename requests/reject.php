<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment'] ?? '');
    
    $stmt = $pdo->prepare("UPDATE requests SET status = 'rejected', processed_by = ?, processed_at = NOW(), admin_comment = ? WHERE id = ? AND status = 'pending'");
    $stmt->execute([$_SESSION['user_id'], $comment, $id]);
    
    $_SESSION['success'] = 'Заявка отклонена';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
$stmt->execute([$id]);
$request = $stmt->fetch();

if (!$request || $request['status'] != 'pending') {
    header('Location: index.php');
    exit;
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Отклонение заявки #<?= $request['id'] ?></h3>
            </div>
            <div class="card-body">
                <p><strong>Оборудование:</strong> <?= htmlspecialchars($request['equipment_name']) ?></p>
                <p><strong>Пользователь:</strong> <?= $request['user_id'] ?></p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Причина отклонения</label>
                        <textarea name="comment" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Подтвердить отклонение</button>
                    <a href="index.php" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>