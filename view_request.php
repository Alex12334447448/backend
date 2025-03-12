<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] == 3) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    $_SESSION['error_message'] = "Ошибка: заявка не указана.";
    header("Location: index.php#featured-car");
    exit();
}

$request_id = $_GET['request_id'];

// Получаем информацию о заявке
$stmt_request = $pdo->prepare("SELECT Заявки.*, Статусы.Имя AS Статус, Автомобили.*
                              FROM Заявки
                              JOIN Статусы ON Заявки.IDСтатуса = Статусы.IDСтатуса
                              JOIN Автомобили ON Заявки.IDАвтомобиля = Автомобили.IDАвтомобиля
                              WHERE Заявки.IDЗаявки = ? AND Заявки.IDПользователя = ?");
$stmt_request->execute([$request_id, $_SESSION['user_id']]);
$request = $stmt_request->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    $_SESSION['error_message'] = "Ошибка: заявка не найдена.";
    header("Location: index.php#featured-car");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статус заявки - ZhukAvto</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2 class="h2 section-title">Статус вашей заявки</h2>
        <div class="car-details">
            <div class="car-image">
                <figure class="card-banner">
                    <img src="<?php echo htmlspecialchars($request['Изображение']); ?>" alt="<?php echo htmlspecialchars($request['Марка'] . ' ' . $request['Модель']); ?>" loading="lazy" class="w-100">
                </figure>
            </div>
            <div class="car-info">
                <h3 class="h3"><?php echo htmlspecialchars($request['Марка'] . ' ' . $request['Модель']); ?> (<?php echo htmlspecialchars($request['Год_выпуска']); ?>)</h3>
                <p class="status-message">Статус: <?php echo htmlspecialchars($request['Статус']); ?></p>
                <p class="status-message">Дата и время просмотра: <?php echo htmlspecialchars($request['Дата_и_время_просмотра']); ?></p>
                <ul class="card-list">
                    <li class="card-list-item"><ion-icon name="people-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($request['Количество_мест']); ?> людей</span></li>
                    <li class="card-list-item"><ion-icon name="flash-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($request['Тип_двигателя']); ?></span></li>
                    <li class="card-list-item"><ion-icon name="speedometer-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($request['Расход_топлива']); ?> л</span></li>
                    <li class="card-list-item"><ion-icon name="hardware-chip-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($request['Трансмиссия']); ?></span></li>
                </ul>
                <p class="card-price"><strong><?php echo htmlspecialchars($request['Цена']); ?>$</strong></p>
            </div>
        </div>
        <a href="index.php#featured-car" class="btn">Вернуться</a>
    </div>
</body>
</html>