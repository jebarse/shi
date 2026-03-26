<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$search = $_GET['search'] ?? '';

$sql = "SELECT id, username, email, full_name, department, role, created_at FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="page-header">
    <h2>Управление пользователями</h2>
</div>

<!-- Поиск -->
<div class="filter-card">
    <form method="GET" class="row g-3">
        <div class="col-md-8">
            <input type="text" class="form-control" name="search" placeholder="Поиск по имени, email или логину" 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Поиск</button>
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
                <th>Логин</th>
                <th>Email</th>
                <th>ФИО</th>
                <th>Подразделение</th>
                <th>Роль</th>
                <th>Дата регистрации</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" class="empty-state">Нет пользователей</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['full_name'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($user['department'] ?: '-') ?></td>
                        <td>
                            <?php if ($user['role'] == 'admin'): ?>
                                <span class="badge bg-danger">Администратор</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Пользователь</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="delete.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить пользователя?')">Удалить</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>