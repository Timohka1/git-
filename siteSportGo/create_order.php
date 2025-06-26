<?php
// create_order.php
include 'config.php';
include 'functions.php';
check_auth();

$equipment = $pdo->query("SELECT * FROM equipment WHERE available_quantity > 0")->fetchAll();
$points = $pdo->query("SELECT * FROM pickup_points")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = (int)$_POST['equipment_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $point_id = (int)$_POST['point_id'];
    $payment_method = $_POST['payment_method'];
    
    // Расчет часов
    $hours = (strtotime($end_time) - strtotime($start_time)) / 3600;
    
    // Получение цены
    $stmt = $pdo->prepare("SELECT price_per_hour FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    $price = $stmt->fetchColumn();
    $total_price = $hours * $price;
    
    // Создание заказа
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (user_id, equipment_id, point_id, start_time, end_time, total_price, payment_method) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $equipment_id,
        $point_id,
        $start_time,
        $end_time,
        $total_price,
        $payment_method
    ]);
    
    // Обновление доступного количества
    $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity - 1 WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа | СпортGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="sports-bg">
            <div class="logo">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            
            <div class="header">
                <h1>Оформление заказа</h1>
                <p>Выбери инвентарь и оформи аренду</p>
            </div>

            <form method="POST" id="orderForm">
                <div class="form-group">
                    <label for="equipment_id">Спортивный инвентарь:</label>
                    <div class="input-icon">
                        <i class="fas fa-basketball"></i>
                        <select name="equipment_id" id="equipment_id" required>
                            <?php foreach($equipment as $item): ?>
                                <option value="<?= $item['equipment_id'] ?>"
                                        data-price="<?= $item['price_per_hour'] ?>">
                                    <?= htmlspecialchars($item['name']) ?> 
                                    (<?= $item['price_per_hour'] ?>₽/час)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="start_time">Дата и время начала аренды:</label>
                    <div class="input-icon">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="datetime-local" name="start_time" id="start_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="end_time">Дата и время окончания аренды:</label>
                    <div class="input-icon">
                        <i class="fas fa-calendar-check"></i>
                        <input type="datetime-local" name="end_time" id="end_time" required>
                    </div>
                    <div class="price-preview" id="pricePreview">
                        Стоимость аренды: <span id="priceValue">0</span>₽
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="point_id">Пункт выдачи:</label>
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <select name="point_id" id="point_id" required>
                            <?php foreach($points as $point): ?>
                                <option value="<?= $point['point_id'] ?>">
                                    <?= htmlspecialchars($point['address']) ?> 
                                    (<?= htmlspecialchars($point['working_hours']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Способ оплаты:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="payment_method" value="cash" checked>
                            <i class="fas fa-money-bill-wave"></i> Наличные
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="card">
                            <i class="fas fa-credit-card"></i> Карта
                        </label>
                    </div>
                </div>
                
                <button class="btn" type="submit">
                    <i class="fas fa-check-circle"></i> Подтвердить заказ
                </button>
            </form>
            
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Вернуться в личный кабинет
            </a>
            
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
        // Расчет стоимости в реальном времени
        const equipmentSelect = document.getElementById('equipment_id');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const pricePreview = document.getElementById('pricePreview');
        const priceValue = document.getElementById('priceValue');
        
        function calculatePrice() {
            const startTime = new Date(startTimeInput.value);
            const endTime = new Date(endTimeInput.value);
            
            if (startTime && endTime && startTime < endTime) {
                const hours = (endTime - startTime) / (1000 * 60 * 60);
                const selectedOption = equipmentSelect.options[equipmentSelect.selectedIndex];
                const pricePerHour = parseFloat(selectedOption.dataset.price);
                const totalPrice = (hours * pricePerHour).toFixed(2);
                
                priceValue.textContent = totalPrice;
                pricePreview.style.display = 'block';
            } else {
                pricePreview.style.display = 'none';
            }
        }
        
        equipmentSelect.addEventListener('change', calculatePrice);
        startTimeInput.addEventListener('change', calculatePrice);
        endTimeInput.addEventListener('change', calculatePrice);
        
        // Установка минимальной даты (текущая дата)
        const now = new Date();
        const minDate = now.toISOString().slice(0, 16);
        startTimeInput.min = minDate;
        endTimeInput.min = minDate;
    </script>
</body>
</html>