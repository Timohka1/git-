<?php
session_start();

// Обработка отправки формы
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST["login"] ?? '';
    $password = $_POST["password"] ?? '';

    try {
        $connection = new PDO("mysql:host=localhost;dbname=demka_db", "root", "");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Подготовка запроса
        $sql = "SELECT * FROM users WHERE login = :login";
        $statement = $connection->prepare($sql);
        $statement->bindParam(':login', $login);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_login'] = $user['login'];
            header("Location: application.php");
            exit();
        } else {
            $_SESSION['error'] = "Неверный логин или пароль";
            header("Location: autho.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка базы данных: " . $e->getMessage();
        header("Location: autho.php");
        exit();
    }
} else {
    // Показать форму авторизации
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1d4ed8;
            --background: #f8f9fa;
            --text: #2d3436;
            --border: #ced4da;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--background);
        }

        .auth-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            color: var(--primary);
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            color: var(--text);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        button {
            width: 100%;
            padding: 1rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #2980b9;
        }

        .auth-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text);
        }

        .auth-link a {
            color: var(--secondary);
            text-decoration: none;
        }

        .auth-link a:hover {
            text-decoration: underline;
        }

        .error {
            color: #e74c3c;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #fee;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST">
            <h2 class="text-center mb-2">Авторизация</h2>
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" required>
            </div>

            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Войти</button>
            <p class="text-center mt-2">
                Уже есть аккаунт? <a href="index.php" class="link">Зарегистрироваться</a>
            </p>
        </form>
    </div>
</body>
</html>
<?php
}
?>