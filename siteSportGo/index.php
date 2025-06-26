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
    
    // Валидация ФИО
    if (empty($full_name)) {
        $errors[] = "ФИО обязательно";
    } elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-\.']+$/u", $full_name)) {
        $errors[] = "ФИО может содержать только буквы, пробелы, дефисы и апострофы";
    }
    
    // Валидация телефона
    if (!validate_phone($phone)) {
        $errors[] = "Неверный формат телефона. Пример: +79123456789";
    }
    
    // Валидация email
    if (!validate_email($email)) {
        $errors[] = "Неверный формат email";
    }
    
    // Валидация пароля
    $pass_length = strlen($_POST['password']);
    if ($pass_length < 8 || $pass_length > 20) {
        $errors[] = "Пароль должен быть от 8 до 20 символов";
    }
    
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<body>
    <div class="container">
        <div class="sports-bg">
            <div class="logo">
                <i class="fa-solid fa-person-running"></i>
            </div>
            
            <div class="header">
                <h1>Регистрация</h1>
                <p>Присоединяйся к спортивному сообществу!</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="full_name">ФИО:</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" id="full_name" required 
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                               placeholder="Иванов Иван Иванович">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон:</label>
                    <div class="input-icon">
                        <i class="fas fa-mobile-alt"></i>
                        <input type="tel" name="phone" id="phone" required 
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                               placeholder="+79123456789">
                    </div>
                    <small>Формат: +79123456789</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="example@mail.ru">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="login">Логин:</label>
                    <div class="input-icon">
                        <i class="fas fa-user-tag"></i>
                        <input type="text" name="login" id="login" required 
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               placeholder="Придумайте логин">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль (8-20 символов):</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" required 
                               minlength="8" maxlength="20"
                               placeholder="Не менее 8 символов">
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter" id="strengthMeter"></div>
                    </div>
                </div>
                
                <button class="btn" type="submit">Зарегистрироваться</button>
            </form>
            
            <div class="login-link">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            </div>
            
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
        // Валидация пароля в реальном времени
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9!@#$%^&*]/.test(password)) strength += 25;
            
            strengthMeter.style.width = strength + '%';
            
            if (strength < 50) {
                strengthMeter.style.backgroundColor = '#e74c3c';
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#f39c12';
            } else {
                strengthMeter.style.backgroundColor = '#27ae60';
            }
        });
        
        // Валидация ФИО
        const fullNameInput = document.getElementById('full_name');
        
        fullNameInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Zа-яА-ЯёЁ\s\-\.']/gu, '');
        });

        document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    
    phoneInput.addEventListener('input', function(e) {
        // Сохраняем позицию курсора
        const cursorPosition = e.target.selectionStart;
        
        // Удаляем все нецифровые символы, кроме плюса
        let numbers = e.target.value.replace(/[^\d+]/g, '');
        
        // Если номер начинается с 8, заменяем на +7
        if (numbers.startsWith('8')) {
            numbers = '+7' + numbers.substring(1);
        }
        // Если номер не начинается с +7, добавляем +7
        else if (!numbers.startsWith('+7') && numbers.length > 0) {
            numbers = '+7' + numbers;
        }
        
        // Форматируем номер
        let formatted = '';
        if (numbers.length > 2) {
            formatted = numbers.substring(0, 2) + ' (' + numbers.substring(2, 5);
        }
        if (numbers.length > 5) {
            formatted += ') ' + numbers.substring(5, 8);
        }
        if (numbers.length > 8) {
            formatted += '-' + numbers.substring(8, 10);
        }
        if (numbers.length > 10) {
            formatted += '-' + numbers.substring(10, 12);
        }
        
        // Обновляем значение
        e.target.value = formatted;
        
        // Восстанавливаем позицию курсора
        const newCursorPosition = cursorPosition + (e.target.value.length - formatted.length);
        e.target.setSelectionRange(newCursorPosition, newCursorPosition);
    });

    phoneInput.addEventListener('blur', function(e) {
        // Проверяем валидность при потере фокуса
        const phone = e.target.value;
        const isValid = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/.test(phone);
        
        if (!isValid) {
            e.target.classList.add('invalid');
            // Можно добавить отображение ошибки
        } else {
            e.target.classList.remove('invalid');
        }
    });
});

           
            phoneInput.addEventListener('change', function(e) {
                e.target.value = e.target.value.replace(/[^\d+()-\s]/g, '');
            });
        });
    </script>
</body>
</html>