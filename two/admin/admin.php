<?php
session_start();
require '../db.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    // Получаем текст из textarea
    $cancellation_reason = isset($_POST['cancellation_reason']) ? $_POST['cancellation_reason'] : '';

    // Обновляем статус и причину отмены
    $stmt = $pdo->prepare("UPDATE requests SET status = ?, cancellation_reason = ? WHERE id = ?");
    $stmt->execute([$status, $cancellation_reason, $request_id]);
    
    header("Location: admin.php?updated=" . $request_id);
    exit;
}

// Получение всех заявок (добавляем cancellation_reason)
$stmt = $pdo->query("SELECT r.*, u.full_name, u.email, u.phone as user_phone 
                     FROM requests r 
                     JOIN users u ON r.user_id = u.id
                     ORDER BY r.date DESC, r.time DESC");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Панель администратора "Мой НЕсам"</h1>
        <p><a href="../logout.php">Выйти</a></p>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="success">Статус заявки #<?= $_GET['updated'] ?> обновлен!</div>
        <?php endif; ?>

        <div class="requests-list">
            <?php if (empty($requests)): ?>
                <p>Нет активных заявок</p>
            <?php else: ?>
                <?php foreach ($requests as $request): ?>
                <div class="request-card">
                    <h3>Заявка #<?= $request['id'] ?></h3>
                    <p><strong>Клиент:</strong> <?= htmlspecialchars($request['full_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($request['email']) ?></p>
                    <p><strong>Телефон:</strong> <?= htmlspecialchars($request['user_phone']) ?></p>
                    <p><strong>Услуга:</strong> <?= htmlspecialchars($request['service']) ?></p>
                    <p><strong>Адрес:</strong> <?= htmlspecialchars($request['address']) ?></p>
                    <p><strong>Дата:</strong> <?= $request['date'] ?> <?= $request['time'] ?></p>
                    <p><strong>Оплата:</strong> <?= $request['payment'] ?></p>
                    <p><strong>Статус:</strong> 
                        <span class="status-<?= $request['status'] ?>">
                            <?= $request['status'] ?>
                        </span>
                    </p>
                    <p><strong>Иная услуга:</strong> <?= htmlspecialchars($request['description']) ?></p>
                    <?php if ($request['status'] == "отменена"): ?>
                        <p><strong>Причина отмены:</strong> <?= htmlspecialchars($request['cancellation_reason']) ?></p>
                    <?php endif; ?>
                    
                    <form method="POST" class="status-form">
                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                        <select name="status" id="select-id">
                            <option value="новая" <?= $request['status'] == 'новая' ? 'selected' : '' ?>>Новая</option>
                            <option value="подтверждена" <?= $request['status'] == 'подтверждена' ? 'selected' : '' ?>>Подтверждена</option>
                            <option value="выполнена" <?= $request['status'] == 'выполнена' ? 'selected' : '' ?>>Выполнена</option>
                            <option value="отменена" <?= $request['status'] == 'отменена' ? 'selected' : '' ?>>Отменена </option>
                        </select>
                        <textarea name="cancellation_reason" id="text-des" style="display: none" class="text-des" placeholder="Причина отмены услуга"></textarea>
                        <button type="submit" name="update_status">Обновить статус</button>
                    </form>
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

        if (selectedIndex === 3) {
            textarea.style.display = textarea.style.display === 'none' ? 'block' : 'none';
        }
    });
</script>
</html>