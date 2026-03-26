<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $new_password = $_POST['new_password'] ?? '';
    
    if ($new_password) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, department = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$full_name, $department, $role, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, department = ?, role = ? WHERE id = ?");
        $stmt->execute([$full_name, $department, $role, $id]);
    }
    
    $success = 'Данные обновлены';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
}
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Редактирование пользователя: <?= htmlspecialchars($user['username']) ?></h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ФИО</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Подразделение</label>
                        <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Роль</label>
                        <select name="role" class="form-select">
                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Пользователь</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Администратор</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Новый пароль (оставьте пустым, если не менять)</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="index.php" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>