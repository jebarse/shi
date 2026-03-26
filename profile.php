<?php
require_once 'includes/config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT username, email, full_name, department FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit;
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Если меняем пароль
        if ($new_password) {
            if (empty($current_password)) {
                $error = 'Введите текущий пароль';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Новый пароль и подтверждение не совпадают';
            } elseif (strlen($new_password) < 6) {
                $error = 'Пароль должен быть не менее 6 символов';
            } else {
                // Проверяем текущий пароль
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $db_user = $stmt->fetch();
                
                if (!password_verify($current_password, $db_user['password'])) {
                    $error = 'Неверный текущий пароль';
                } else {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, department = ?, password = ? WHERE id = ?");
                    $stmt->execute([$full_name, $department, $hash, $user_id]);
                    $success = 'Данные и пароль успешно обновлены';
                    
                    // Обновляем данные в сессии
                    $_SESSION['user_name'] = $user['username'];
                }
            }
        } else {
            // Только обновление профиля
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, department = ? WHERE id = ?");
            $stmt->execute([$full_name, $department, $user_id]);
            $success = 'Данные успешно обновлены';
        }
        
        // Обновляем данные для отображения
        $stmt = $pdo->prepare("SELECT username, email, full_name, department FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
    } catch (PDOException $e) {
        $error = 'Ошибка при обновлении: ' . $e->getMessage();
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Мой профиль</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Личные данные</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Логин</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            <small class="text-muted">Логин нельзя изменить</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <small class="text-muted">Email нельзя изменить</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ФИО</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Подразделение</label>
                            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="mb-3">Смена пароля</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Текущий пароль</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Оставьте пустым, если не меняете">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    <a href="index.php" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>