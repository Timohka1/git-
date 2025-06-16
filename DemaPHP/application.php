<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="static/bootstrap.min.css">
    <script src="js/bootstrap.bundle.min.js" defer></script>
    <style>
        .violation-card {
            margin: 1rem 0;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: #fff;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        .status-new { background: #ffd700; color: #000; }
        .status-confirmed { background: #90EE90; color: #006400; }
        .status-rejected { background: #ffcccb; color: #8b0000; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="img/car.jpg" alt="Logo" width="75" height="75">
                Нарушениям.Нет
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="statements.php">Мои заявки</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        try {
            $connection = new PDO("mysql:host=localhost;dbname=demka_db", "root", "");
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $user_id = $_SESSION['user_id'];
            $sql = "SELECT * FROM statements WHERE user_id = :user_id";
            $stmt = $connection->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);

            if ($stmt->rowCount() > 0) {
                echo '<h2 class="mb-4">Мои заявления о нарушениях</h2>';
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $status_class = 'status-' . $row['status'];
                    $status_text = match($row['status']) {
                        'new' => 'На рассмотрении',
                        'confirmed' => 'Подтверждено',
                        'rejected' => 'Отклонено',
                        default => 'Неизвестный статус'
                    };
        ?>
                    <div class="violation-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="mb-0">Номер авто: <?= htmlspecialchars($row['carNumber']) ?></h5>
                            <span class="status-badge <?= $status_class ?>">
                                <?= $status_text ?>
                            </span>
                        </div>

                        <p class="mb-0"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                    </div>
        <?php
                }
            } else {
                echo '<div class="alert alert-info">У вас нет активных заявлений</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Ошибка: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</body>
</html>