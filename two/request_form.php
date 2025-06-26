<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

// Обработка создания заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request'])) {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $service = $_POST['service'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $payment = $_POST['payment'];
    $description = $_POST['description'];

    // Валидация даты
    if (strtotime($date) < strtotime('today')) {
        $error = "Дата не может быть в прошлом";
    } else {
        // Параметр для описания
        $cancellation_reason = "";

        // Проверка на необходимость описания
        if ($service === "Имя услуги" && empty($description)) {
            $error = "Для иной услуги необходимо указать описание";
        } else {
            $stmt = $pdo->prepare("INSERT INTO requests (user_id, address, phone, service, date, time, payment, description, cancellation_reason, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'новая')");
            // Execute query with the new correct variable name
            $stmt->execute([$user_id, $address, $phone, $service, $date, $time, $payment, $description, $cancellation_reason]);
            header("Location: request_form.php?success=1");
            exit;
        }
    }
}

// Получение заявок пользователя
$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY date DESC, time DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Мои заявки "Мой НЕсам"</h1>
        <p><a href="logout.php">Выйти</a></p>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Заявка успешно создана!</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="request-form">
            <h2>Новая заявка</h2>
            <form method="POST">
                <input type="text" name="address" placeholder="Адрес" required>
                <input type="tel" name="phone" placeholder="Телефон" required>
                <select name="service" required id="select-id">
                    <option value="Общий клининг">Общий клининг</option>
                    <option value="Генеральная уборка">Генеральная уборка</option>
                    <option value="Послестроительная уборка">Послестроительная уборка</option>
                    <option value="Химчистка">Химчистка ковров и мебели</option>
                    <option value="Иная услуга">Иная услуга</option>
                </select>
                <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
                <input type="time" name="time" required>
                <select name="payment" required>
                    <option value="Наличные">Наличные</option>
                    <option value="Карта">Банковская карта</option>
                </select>
                <textarea name="description" id="text-des" style="display: none" placeholder="Описание иной услуги"></textarea>
                <button type="submit" name="create_request">Создать заявку</button>
            </form>
        </div>
        
        <div class="requests-list">
            <h2>История заявок</h2>
            <?php if (empty($requests)): ?>
                <p>У вас пока нет заявок</p>
            <?php else: ?>
                <?php foreach ($requests as $request): ?>
                <div class="request-card">
                    <p><strong>Услуга:</strong> <?= htmlspecialchars($request['service']) ?></p>
                    <p><strong>Адрес:</strong> <?= htmlspecialchars($request['address']) ?></p>
                    <p><strong>Дата:</strong> <?= $request['date'] ?> <?= $request['time'] ?></p>
                    <p><strong>Статус:</strong> 
                        <span class="status-<?= $request['status'] ?>">
                            <?= $request['status'] ?>
                        </span>
                    </p>
                    <?php if ($request['status'] == "отменена"): ?>
                        <p><strong>Причина отмены:</strong> <?= $request['cancellation_reason'] ?> <?= $request['cancellation_reason'] ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

<script>
    const select = document.getElementById("select-id");
    const textarea = document.getElementById("text-des");

    select.addEventListener("change", function() {
        const selectedIndex = select.selectedIndex;
        console.log("Choose option: " + selectedIndex);

        if (selectedIndex === 4) {
            textarea.style.display = textarea.style.display === 'none' ? 'block' : 'none';
        }
    });
</script>
</html>