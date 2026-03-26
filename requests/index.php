<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT r.*, u.username 
        FROM requests r 
        JOIN users u ON r.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $sql .= " AND (r.equipment_name LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY 
            CASE r.status 
                WHEN 'pending' THEN 1 
                ELSE 2 
            END, 
            r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Статистика
$stats = [
    'pending' => $pdo->query("SELECT COUNT(*) FROM requests WHERE status='pending'")->fetchColumn(),
    'approved' => $pdo->query("SELECT COUNT(*) FROM requests WHERE status='approved'")->fetchColumn(),
    'rejected' => $pdo->query("SELECT COUNT(*) FROM requests WHERE status='rejected'")->fetchColumn(),
    'issued' => $pdo->query("SELECT COUNT(*) FROM requests WHERE status='issued'")->fetchColumn(),
];
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="page-header">
    <h2>Управление заявками</h2>
    <div>
        <span class="badge bg-warning">Ожидают: <?= $stats['pending'] ?></span>
        <span class="badge bg-success">Одобрены: <?= $stats['approved'] ?></span>
        <span class="badge bg-danger">Отклонены: <?= $stats['rejected'] ?></span>
        <span class="badge bg-info">Выданы: <?= $stats['issued'] ?></span>
    </div>
</div>

<!-- Фильтры -->
<div class="filter-card">
    <form method="GET" class="row g-3">
        <div class="col-md-5">
            <input type="text" class="form-control" name="search" placeholder="Поиск по оборудованию или пользователю" 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status">
                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Все статусы</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Ожидают</option>
                <option value="approved" <?= $status_filter == 'approved' ? 'selected' : '' ?>>Одобрены</option>
                <option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Отклонены</option>
                <option value="issued" <?= $status_filter == 'issued' ? 'selected' : '' ?>>Выданы</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Фильтр</button>
        </div>
        <div class="col-md-2">
            <a href="index.php" class="btn btn-secondary w-100">Сбросить</a>
        </div>
    </form>
</div>

<!-- Таблица -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
             аутох
                <th>ID</th>
                <th>Пользователь</th>
                <th>Оборудование</th>
                <th>Кол-во</th>
                <th>Причина</th>
                <th>Дата</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="8" class="empty-state">Нет заявок</td>
                </tr>
            <?php else: ?>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?= $req['id'] ?></td>
                        <td><?= htmlspecialchars($req['username']) ?></td>
                        <td><?= htmlspecialchars($req['equipment_name']) ?></td>
                        <td><?= $req['quantity'] ?></td>
                        <td><?= htmlspecialchars(mb_substr($req['reason'], 0, 50)) ?>...</td>
                        <td><?= date('d.m.Y', strtotime($req['created_at'])) ?></td>
                        <td>
                            <?php
                            $status_badge = [
                                'pending' => ['bg-warning', 'Ожидает'],
                                'approved' => ['bg-success', 'Одобрена'],
                                'rejected' => ['bg-danger', 'Отклонена'],
                                'issued' => ['bg-info', 'Выдана']
                            ];
                            $badge = $status_badge[$req['status']] ?? ['bg-secondary', $req['status']];
                            ?>
                            <span class="badge <?= $badge[0] ?>"><?= $badge[1] ?></span>
                        </td>
                        <td>
                            <a href="view.php?id=<?= $req['id'] ?>" class="btn btn-sm btn-info">Просмотр</a>
                            <?php if ($req['status'] == 'pending'): ?>
                                <a href="approve.php?id=<?= $req['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Одобрить заявку?')">Одобрить</a>
                                <a href="reject.php?id=<?= $req['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Отклонить заявку?')">Отклонить</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>