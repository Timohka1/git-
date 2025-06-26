<?php
// register.php
include 'config.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $login = trim($_POST['login']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $errors = [];
    
    if (empty($full_name)) $errors[] = "ФИО обязательно";
    if (!validate_phone($phone)) $errors[] = "Неверный формат телефона";
    if (!validate_email($email)) $errors[] = "Неверный формат email";
    if (strlen($_POST['password']) < 6) $errors[] = "Пароль должен быть не менее 6 символов";
    
    // Проверка уникальности
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR login = ?");
    $stmt->execute([$email, $login]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Email или логин уже используются";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, phone, email, login, password) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$full_name, $phone, $email, $login, $password]);
        
        $_SESSION['success'] = "Регистрация прошла успешно! Теперь войдите в систему.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | СпортGo</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; }
        .form-group { margin-bottom: 30px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: rgb(216, 219, 52); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background:rgb(80, 206, 42); }
        .error { color: #e74c3c; margin-bottom: 10px; }
        .success { color: #27ae60; margin-bottom: 10px; }
        .login-link { text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Регистрация</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>ФИО:</label>
                <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Телефон ("+7(XXX)XXX XX"):</label>
                <input type="tel" name="phone" required pattern="\+7\d{10}" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" name="login" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Пароль (мин. 6 символов):</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <button type="submit">Зарегистрироваться</button>
        </form>
        
        <div class="login-link">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </div>
</body>
</html>