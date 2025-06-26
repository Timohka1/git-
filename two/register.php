<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $login = $_POST['login'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (full_name, phone, email, login, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $phone, $email, $login, $password]);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            
            phoneInput.addEventListener('input', function(e) {
                let number = e.target.value.replace(/\D/g, '');
                
                
                if(number.startsWith('8') && number.length > 1) {
                    number = '7' + number.substring(1);
                }
                
               
                let formatted = '+7';
                if(number.length > 1) {
                    formatted += ' (' + number.substring(1, 4);
                }
                if(number.length >= 5) {
                    formatted += ') ' + number.substring(4, 7);
                }
                if(number.length >= 8) {
                    formatted += '-' + number.substring(7, 9);
                }
                if(number.length >= 10) {
                    formatted += '-' + number.substring(9, 11);
                }
                
                
                e.target.value = formatted;
            });

           
            phoneInput.addEventListener('change', function(e) {
                e.target.value = e.target.value.replace(/[^\d+()-\s]/g, '');
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Регистрация "Мой НЕсам"</h1>
        <form method="POST">
            <input type="text" name="full_name" placeholder="ФИО" required>
            <input type="tel" name="phone" placeholder="Телефон" id="phone" required 
                       placeholder="+7 (999) 999-99-99" maxlength="18" minlength="5">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p>Уже есть аккаунт? <a href="index.php">Войти</a></p>
    </div>
</body>
</html>