<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] == 3) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['car_id']) || !is_numeric($_GET['car_id'])) {
    $_SESSION['error_message'] = "Ошибка: автомобиль не указан.";
    header("Location: index.php#featured-car");
    exit();
}

$car_id = $_GET['car_id'];

// Получаем информацию об автомобиле
$stmt_car = $pdo->prepare("SELECT * FROM Автомобили WHERE IDАвтомобиля = ?");
$stmt_car->execute([$car_id]);
$car = $stmt_car->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    $_SESSION['error_message'] = "Ошибка: автомобиль не найден.";
    header("Location: index.php#featured-car");
    exit();
}

// Проверяем существующие заявки на этот автомобиль
$stmt_requests = $pdo->prepare("SELECT Заявки.*, Статусы.Имя AS Статус 
                               FROM Заявки 
                               JOIN Статусы ON Заявки.IDСтатуса = Статусы.IDСтатуса 
                               WHERE Заявки.IDАвтомобиля = ? 
                               ORDER BY Заявки.Дата_и_время_просмотра DESC LIMIT 1");
$stmt_requests->execute([$car_id]);
$latest_request = $stmt_requests->fetch(PDO::FETCH_ASSOC);

$status = $latest_request ? $latest_request['Статус'] : 'Доступен'; // Если заявок нет, считаем автомобиль доступным
$can_book = ($status === 'Доступен' && !$latest_request); // Можно записаться, только если статус "Доступен" и нет заявок
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
        <h2 class="h2 section-title">Статус заявки на автомобиль</h2>
        <div class="car-details">
            <div class="car-image">
                <figure class="card-banner">
                    <img src="<?php echo htmlspecialchars($car['Изображение']); ?>" alt="<?php echo htmlspecialchars($car['Марка'] . ' ' . $car['Модель']); ?>" loading="lazy" class="w-100">
                </figure>
            </div>
            <div class="car-info">
                <h3 class="h3"><?php echo htmlspecialchars($car['Марка'] . ' ' . $car['Модель']); ?> (<?php echo htmlspecialchars($car['Год_выпуска']); ?>)</h3>
                <ul class="card-list">
                    <li class="card-list-item"><ion-icon name="people-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($car['Количество_мест']); ?> людей</span></li>
                    <li class="card-list-item"><ion-icon name="flash-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($car['Тип_двигателя']); ?></span></li>
                    <li class="card-list-item"><ion-icon name="speedometer-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($car['Расход_топлива']); ?> л</span></li>
                    <li class="card-list-item"><ion-icon name="hardware-chip-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($car['Трансмиссия']); ?></span></li>
                </ul>
                <p class="card-price"><strong><?php echo htmlspecialchars($car['Цена']); ?>$</strong></p>
            </div>
        </div>

        <?php if ($latest_request && $status === 'В пути'): ?>
            <p class="status-message">Автомобиль "В пути". Запись на просмотр недоступна.</p>
        <?php elseif ($latest_request && $status === 'Недоступен'): ?>
            <p class="status-message">Автомобиль "Недоступен". Запись на просмотр недоступна.</p>
        <?php elseif ($latest_request && $status === 'Доступен'): ?>
            <p class="status-message">Время занято другим пользователем. Попробуйте выбрать другое время.</p>
        <?php else: ?>
            <form action="submit_request.php" method="POST" class="request-form">
                <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($car['IDАвтомобиля']); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="input-wrapper">
                    <label for="viewing_date" class="input-label">Дата и время просмотра</label>
                    <input type="datetime-local" name="viewing_date" id="viewing_date" class="input-field" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Записаться</button>
                    <a href="index.php#featured-car" class="btn">Отмена</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>