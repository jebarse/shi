<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Получаем параметры фильтрации
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Формируем запрос
$sql = "SELECT * FROM equipment WHERE 1=1";
$params = [];

if ($type) {
    $sql .= " AND type = ?";
    $params[] = $type;
}

if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (name LIKE ? OR serial_number LIKE ? OR inventory_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$equipment = $stmt->fetchAll();

// Получаем уникальные типы для фильтра
$types = $pdo->query("SELECT DISTINCT type FROM equipment ORDER BY type")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Доступное оборудование</h2>
        <p class="text-muted">Здесь вы можете посмотреть список всего оборудования и создать заявку на выдачу</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="create-request.php" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Создать заявку
        </a>
    </div>
</div>

<!-- Фильтры -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" 
                       placeholder="Поиск по названию, серийному или инвентарному номеру" 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="type">
                    <option value="">Все типы</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t['type'] ?>" <?= $type == $t['type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Все статусы</option>
                    <option value="available" <?= $status == 'available' ? 'selected' : '' ?>>В наличии</option>
                    <option value="issued" <?= $status == 'issued' ? 'selected' : '' ?>>Выдано</option>
                    <option value="repair" <?= $status == 'repair' ? 'selected' : '' ?>>В ремонте</option>
                    <option value="broken" <?= $status == 'broken' ? 'selected' : '' ?>>Сломано</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Поиск</button>
            </div>
        </form>
    </div>
</div>

<!-- Таблица оборудования -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
             аутох
                <th>ID</th>
                <th>Название</th>
                <th>Тип</th>
                <th>Серийный номер</th>
                <th>Инв. номер</th>
                <th>Статус</th>
                <th>Местоположение</th>
                <th>Действие</th>
            </thead>
        <tbody>
            <?php if (empty($equipment)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Оборудование не найдено
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($equipment as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                    <td><?= htmlspecialchars($item['type']) ?></td>
                    <td><?= htmlspecialchars($item['serial_number'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($item['inventory_number'] ?: '-') ?></td>
                    <td>
                        <?php
                        $status_labels = [
                            'available' => ['bg-success', 'В наличии'],
                            'issued' => ['bg-warning', 'Выдано'],
                            'repair' => ['bg-secondary', 'В ремонте'],
                            'broken' => ['bg-danger', 'Сломано'],
                            'written_off' => ['bg-dark', 'Списано']
                        ];
                        $label = $status_labels[$item['status']] ?? ['bg-secondary', $item['status']];
                        ?>
                        <span class="badge <?= $label[0] ?>"><?= $label[1] ?></span>
                    </td>
                    <td><?= htmlspecialchars($item['location'] ?: '-') ?></td>
                    <td>
                        <?php if ($item['status'] == 'available'): ?>
                            <a href="create-request.php?equipment_id=<?= $item['id'] ?>" 
                               class="btn btn-sm btn-success" title="Запросить">
                                <i class="fas fa-hand-holding-heart"></i> Запросить
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled title="Недоступно">
                                <i class="fas fa-ban"></i> Недоступно
                            </button>
                        <?php endif; ?>
                        
                        <!-- Кнопка просмотра деталей -->
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                data-bs-target="#detailsModal<?= $item['id'] ?>" title="Подробнее">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </td>
                </tr>
                
                <!-- Модальное окно с деталями -->
                <div class="modal fade" id="detailsModal<?= $item['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">Детальная информация</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-sm">
                                    <tr><th>Название:</th><td><?= htmlspecialchars($item['name']) ?></td></tr>
                                    <tr><th>Тип:</th><td><?= htmlspecialchars($item['type']) ?></td></tr>
                                    <tr><th>Серийный номер:</th><td><?= htmlspecialchars($item['serial_number'] ?: '-') ?></td></tr>
                                    <tr><th>Инвентарный номер:</th><td><?= htmlspecialchars($item['inventory_number'] ?: '-') ?></td></tr>
                                    <tr><th>Местоположение:</th><td><?= htmlspecialchars($item['location'] ?: '-') ?></td></tr>
                                    <tr><th>Дата покупки:</th><td><?= $item['purchase_date'] ? date('d.m.Y', strtotime($item['purchase_date'])) : '-' ?></td></tr>
                                    <tr><th>Гарантия до:</th><td><?= $item['warranty_until'] ? date('d.m.Y', strtotime($item['warranty_until'])) : '-' ?></td></tr>
                                    <tr><th>Примечания:</th><td><?= htmlspecialchars($item['notes'] ?: '-') ?></td></tr>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <?php if ($item['status'] == 'available'): ?>
                                    <a href="create-request.php?equipment_id=<?= $item['id'] ?>" class="btn btn-success">Запросить</a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>