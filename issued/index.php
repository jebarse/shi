<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'active';

$sql = "SELECT ie.*, u.username as user_name, a.username as admin_name 
        FROM issued_equipment ie 
        JOIN users u ON ie.issued_to_user_id = u.id 
        JOIN users a ON ie.issued_by_admin_id = a.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (ie.equipment_name LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter == 'active') {
    $sql .= " AND ie.actual_return_date IS NULL";
} elseif ($status_filter == 'returned') {
    $sql .= " AND ie.actual_return_date IS NOT NULL";
}

$sql .= " ORDER BY ie.issued_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$issued = $stmt->fetchAll();

$stats = [
    'active' => $pdo->query("SELECT COUNT(*) FROM issued_equipment WHERE actual_return_date IS NULL")->fetchColumn(),
    'returned' => $pdo->query("SELECT COUNT(*) FROM issued_equipment WHERE actual_return_date IS NOT NULL")->fetchColumn(),
];
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="page-header">
    <h2>Выданное оборудование</h2>
    <div>
        <span class="badge bg-warning">Активных: <?= $stats['active'] ?></span>
        <span class="badge bg-secondary">Возвращено: <?= $stats['returned'] ?></span>
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
                <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Активные выдачи</option>
                <option value="returned" <?= $status_filter == 'returned' ? 'selected' : '' ?>>Возвращённые</option>
                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Все записи</option>
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

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Оборудование</th>
                <th>Кому выдано</th>
                <th>Кто выдал</th>
                <th>Дата выдачи</th>
                <th>Срок возврата</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($issued)): ?>
                <tr>
                    <td colspan="8" class="empty-state">Нет записей</td>
                </tr>
            <?php else: ?>
                <?php foreach ($issued as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['equipment_name']) ?></td>
                        <td><?= htmlspecialchars($item['user_name']) ?></td>
                        <td><?= htmlspecialchars($item['admin_name']) ?></td>
                        <td><?= date('d.m.Y', strtotime($item['issued_at'])) ?></td>
                        <td><?= $item['expected_return_date'] ? date('d.m.Y', strtotime($item['expected_return_date'])) : '-' ?></td>
                        <td>
                            <?php if ($item['actual_return_date']): ?>
                                <span class="badge bg-secondary">Возвращено <?= date('d.m.Y', strtotime($item['actual_return_date'])) ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning">В пользовании</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$item['actual_return_date']): ?>
                                <a href="return.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Отметить возврат?')">Вернуть</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>