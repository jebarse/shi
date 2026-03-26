<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>Мои заявки</h2>

<?php if (empty($requests)): ?>
    <div class="alert alert-info">У вас пока нет ни одной заявки.</div>
    <a href="create-request.php" class="btn btn-primary">Создать заявку</a>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Оборудование</th>
                    <th>Кол-во</th>
                    <th>Дата заявки</th>
                    <th>Статус</th>
                    <th>Комментарий админа</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <?php
                    $status_badge = [
                        'pending' => ['bg-warning', 'Ожидает'],
                        'approved' => ['bg-success', 'Одобрена'],
                        'rejected' => ['bg-danger', 'Отклонена'],
                        'issued' => ['bg-info', 'Выдана'],
                        'returned' => ['bg-secondary', 'Возвращена']
                    ];
                    $badge = $status_badge[$req['status']] ?? ['bg-secondary', $req['status']];
                    ?>
                    <tr>
                        <td><?= $req['id'] ?></td>
                        <td><?= htmlspecialchars($req['equipment_name']) ?></td>
                        <td><?= $req['quantity'] ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($req['created_at'])) ?></td>
                        <td><span class="badge <?= $badge[0] ?>"><?= $badge[1] ?></span></td>
                        <td><?= htmlspecialchars($req['admin_comment'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>