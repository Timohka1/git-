<?php
// dashboard.php
include 'config.php';
include 'functions.php';
check_auth();

$orders = get_user_orders($_SESSION['user_id'], $pdo);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | СпортGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="sports-bg">
            <header>
                <h1 class="welcome">Добро пожаловать, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
                <div class="user-actions">
                    <a href="create_order.php" class="btn">
                        <i class="fas fa-plus-circle"></i> Создать заказ
                    </a>
                    <a href="logout.php" class="btn logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </a>
                </div>
            </header>
            
            <h2 class="page-title">История заказов</h2>
            
            <?php if (count($orders) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-basketball"></i> Инвентарь</th>
                            <th><i class="fas fa-calendar-start"></i> Дата начала</th>
                            <th><i class="fas fa-calendar-check"></i> Дата окончания</th>
                            <th><i class="fas fa-map-marker-alt"></i> Пункт выдачи</th>
                            <th><i class="fas fa-money-bill-wave"></i> Стоимость</th>
                            <th><i class="fas fa-info-circle"></i> Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['equipment_name']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['start_time'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['end_time'])) ?></td>
                                <td><?= htmlspecialchars($order['address']) ?></td>
                                <td><?= number_format($order['total_price'], 2) ?>₽</td>
                                <td>
                                    <span class="status status-<?= $order['status'] ?>">
                                        <?php 
                                        $statuses = [
                                            'new' => 'Новый',
                                            'confirmed' => 'Подтвержден',
                                            'completed' => 'Выполнен',
                                            'cancelled' => 'Отменен'
                                        ];
                                        echo $statuses[$order['status']];
                                        ?>
                                    </span>
                                    <?php if ($order['status'] == 'cancelled' && !empty($order['cancellation_reason'])): ?>
                                        <div class="cancellation-reason">
                                            <strong>Причина:</strong> <?= htmlspecialchars($order['cancellation_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-orders">
                    <p><i class="fas fa-shopping-cart fa-2x"></i></p>
                    <p>У вас пока нет заказов</p>
                    <a href="create_order.php" class="create-order-btn">
                        </i> Оформить первый заказ
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="sports-icons">
                <i class="fas fa-football-ball"></i>
                <i class="fas fa-basketball-ball"></i>
                <i class="fas fa-baseball-ball"></i>
                <i class="fas fa-volleyball-ball"></i>
                <i class="fas fa-running"></i>
            </div>
        </div>
    </div>
</body>
</html>