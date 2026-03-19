<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();

// Статистика
$stats = [
    'total_equipment' => $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn(),
    'available' => $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'available'")->fetchColumn(),
    'issued' => $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'issued'")->fetchColumn(),
    'repair' => $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'repair'")->fetchColumn(),
    'pending_requests' => $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
];

// Последние заявки
$recent_requests = $pdo->query("
    SELECT r.*, u.username 
    FROM requests r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();

// Последние выдачи
$recent_issued = $pdo->query("
    SELECT ie.*, u.username as user_name, a.username as admin_name
    FROM issued_equipment ie
    JOIN users u ON ie.issued_to_user_id = u.id
    JOIN users a ON ie.issued_by_admin_id = a.id
    ORDER BY ie.issued_at DESC
    LIMIT 5
")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h1 class="mb-4">Панель администратора</h1>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Всего оборудования</h5>
                <h2><?= $stats['total_equipment'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">В наличии</h5>
                <h2><?= $stats['available'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Ожидающих заявок</h5>
                <h2><?= $stats['pending_requests'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Пользователей</h5>
                <h2><?= $stats['total_users'] ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Быстрые ссылки -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Быстрые действия</h5>
            </div>
            <div class="card-body">
                <a href="/admin/equipment/add.php" class="btn btn-success me-2">➕ Добавить оборудование</a>
                <a href="/admin/requests/index.php?status=pending" class="btn btn-warning me-2">⏳ Новые заявки</a>
                <a href="/admin/issued/index.php" class="btn btn-info me-2">📋 Выданное</a>
                <a href="/admin/users/index.php" class="btn btn-secondary">👥 Пользователи</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Последние заявки -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5>Последние заявки</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>Оборудование</th>
                                <th>Статус</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_requests as $req): ?>
                            <tr>
                                <td><?= $req['id'] ?></td>
                                <td><?= htmlspecialchars($req['username']) ?></td>
                                <td><?= htmlspecialchars($req['equipment_name']) ?></td>
                                <td>
                                    <?php
                                    $status_badge = [
                                        'pending' => 'bg-warning',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'issued' => 'bg-info',
                                        'returned' => 'bg-secondary'
                                    ];
                                    ?>
                                    <span class="badge <?= $status_badge[$req['status']] ?>">
                                        <?= $req['status'] ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($req['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="/admin/requests/index.php" class="btn btn-sm btn-primary">Все заявки →</a>
            </div>
        </div>
    </div>

    <!-- Последние выдачи -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5>Последние выдачи</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Оборудование</th>
                                <th>Кому</th>
                                <th>Кто выдал</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_issued as $iss): ?>
                            <tr>
                                <td><?= htmlspecialchars($iss['equipment_name']) ?></td>
                                <td><?= htmlspecialchars($iss['user_name']) ?></td>
                                <td><?= htmlspecialchars($iss['admin_name']) ?></td>
                                <td><?= date('d.m.Y', strtotime($iss['issued_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="/admin/issued/index.php" class="btn btn-sm btn-primary">Все выдачи →</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>