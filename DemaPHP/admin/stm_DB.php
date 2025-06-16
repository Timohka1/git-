<?php
session_start();

// Обработка изменения статуса
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['statement_id'])) {
    try {
        $connection = new PDO("mysql:host=localhost;dbname=demka_db", "root", "");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $statement_id = $_POST['statement_id'];
        $new_status = $_POST['status'];
        
        $sql = "UPDATE statements SET status = :status WHERE id = :id";
        $stmt = $connection->prepare($sql);
        $stmt->execute([':status' => $new_status, ':id' => $statement_id]);
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        die("Ошибка обновления: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../img/car.jpg" alt="Logo" width="75" height="75" class="d-inline-block align-text-center">
                Нарушениям.Нет
            </a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="stm_DB.php">Заявления</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>База данных заявлений</h1>
        <p>Управление статусами заявок</p>
        
        <?php 
        try {
            $connection = new PDO("mysql:host=localhost;dbname=demka_db", "root", "");
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "SELECT * FROM statements";
            $result = $connection->query($sql);

            if ($result && $result->rowCount() > 0) {
                foreach ($result as $row) {
        ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="statement_id" value="<?= htmlspecialchars($row['id']) ?>">
                                
                                <h5 class="card-title">
                                    Номер автомобиля: <?= htmlspecialchars($row['carNumber']) ?>
                                </h5>
                                
                                <p class="card-text">
                                    <?= htmlspecialchars($row['description']) ?>
                                </p>
                                
                                <div class="mb-3">
                                    <label class="form-label">Статус:</label>
                                    <select name="status" class="form-select">
                                        <option value="new" <?= $row['status'] == 'new' ? 'selected' : '' ?>>Новое</option>
                                        <option value="confirmed" <?= $row['status'] == 'confirmed' ? 'selected' : '' ?>>Подтверждено</option>
                                        <option value="rejected" <?= $row['status'] == 'rejected' ? 'selected' : '' ?>>Отклонено</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Обновить статус</button>
                            </form>
                        </div>
                    </div>
        <?php 
                }
            } else {
                echo "<p>Заявления не найдены</p>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Ошибка подключения: ".htmlspecialchars($e->getMessage())."</div>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>