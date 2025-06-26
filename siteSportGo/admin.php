<?php
// admin.php
include 'config.php';
include 'functions.php';
check_admin();

$orders = get_all_orders($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'];
    $cancellation_reason = ($new_status === 'cancelled') ? trim($_POST['cancellation_reason']) : null;
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, cancellation_reason = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $cancellation_reason, $order_id]);
    
    if ($new_status === 'cancelled' || $new_status === 'completed') {
        $stmt = $pdo->prepare("
            UPDATE equipment e
            JOIN orders o ON e.equipment_id = o.equipment_id
            SET e.available_quantity = e.available_quantity + 1
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
    }
    
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора | СпортGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="sports-bg">
            <header>
                <h1 class="header-title">Панель администратора</h1>
                <a href="logout.php" class="btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Выйти
                </a>
            </header>
            
            <h2 class="page-title">Все заказы</h2>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Клиент</th>
                        <th><i class="fas fa-phone"></i> Телефон</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-basketball"></i> Инвентарь</th>
                        <th><i class="fas fa-calendar-alt"></i> Период аренды</th>
                        <th><i class="fas fa-map-marker-alt"></i> Пункт выдачи</th>
                        <th><i class="fas fa-money-bill-wave"></i> Стоимость</th>
                        <th><i class="fas fa-info-circle"></i> Статус</th>
                        <th><i class="fas fa-cog"></i> Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="ID"><?= $order['order_id'] ?></td>
                            <td data-label="Клиент"><?= htmlspecialchars($order['full_name']) ?></td>
                            <td data-label="Телефон"><?= htmlspecialchars($order['phone']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($order['email']) ?></td>
                            <td data-label="Инвентарь"><?= htmlspecialchars($order['equipment_name']) ?></td>
                            <td data-label="Период аренды" class="period-cell">
                                <?= date('d.m.Y H:i', strtotime($order['start_time'])) ?> -<br>
                                <?= date('d.m.Y H:i', strtotime($order['end_time'])) ?>
                            </td>
                            <td data-label="Пункт выдачи"><?= htmlspecialchars($order['address']) ?></td>
                            <td data-label="Стоимость"><?= number_format($order['total_price'], 2) ?>₽</td>
                            <td data-label="Статус">
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?php 
                                    $statuses = [
                                        'new' => 'Новый',
                                        'confirmed' => 'Подтвержден',
                                        'completed' => 'Выполнен',
                                        'cancelled' => 'Отменен'
                                    ];
                                    echo $statuses[$order['status']];
                                    ?>
                                    <?php if ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])): ?>
                                        <br><small>Причина: <?= htmlspecialchars($order['cancellation_reason']) ?></small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td data-label="Действия">
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="new_status" class="status-select" onchange="toggleCancelReason(this, <?= $order['order_id'] ?>)">
                                        <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>>Новый</option>
                                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Подтвержден</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Выполнен</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                                    </select>
                                    <div id="cancel-reason-<?= $order['order_id'] ?>" class="cancel-reason" style="display: <?= $order['status'] === 'cancelled' ? 'block' : 'none' ?>;">
                                        <textarea name="cancellation_reason" placeholder="Укажите причину отмены" class="form-control"><?= htmlspecialchars($order['cancellation_reason'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" name="update_status" class="update-btn">
                                        <i class="fas fa-sync-alt"></i> Обновить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="sports-icons">
                <i class="fas fa-football-ball"></i>
                <i class="fas fa-basketball-ball"></i>
                <i class="fas fa-baseball-ball"></i>
                <i class="fas fa-volleyball-ball"></i>
                <i class="fas fa-running"></i>
            </div>
        </div>
    </div>

    <script>
        function toggleCancelReason(select, orderId) {
            const reasonField = document.getElementById(`cancel-reason-${orderId}`);
            if (select.value === 'cancelled') {
                reasonField.style.display = 'block';
            } else {
                reasonField.style.display = 'none';
            }
        }
    </script>
</body>
</html>