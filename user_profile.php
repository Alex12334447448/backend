<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] == 3) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Отмена заявки
if (isset($_GET['cancel_request_id'])) {
    $request_id = $_GET['cancel_request_id'];
    $stmt_cancel = $pdo->prepare("DELETE FROM Заявки WHERE IDЗаявки = ? AND IDПользователя = ?");
    $stmt_cancel->execute([$request_id, $user_id]);
    $_SESSION['success_message'] = "Заявка успешно отменена.";
    header("Location: user_profile.php");
    exit();
}

// Получаем заявки пользователя
$stmt_requests = $pdo->prepare("SELECT Заявки.*, Статусы.Имя AS Статус, Автомобили.Марка, Автомобили.Модель, Автомобили.Изображение
                                FROM Заявки
                                JOIN Статусы ON Заявки.IDСтатуса = Статусы.IDСтатуса
                                JOIN Автомобили ON Заявки.IDАвтомобиля = Автомобили.IDАвтомобиля
                                WHERE Заявки.IDПользователя = ?");
$stmt_requests->execute([$user_id]);
$requests = $stmt_requests->fetchAll(PDO::FETCH_ASSOC);

// Получаем уведомления для пользователя
$stmt_notifications = $pdo->prepare("SELECT * FROM Уведомления WHERE IDПользователя = ? ORDER BY Дата_уведомления DESC");
$stmt_notifications->execute([$user_id]);
$notifications = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - ZhukAvto</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2 class="h2 section-title">Личный кабинет</h2>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div style="text-align: center; padding: 10px; background: var(--honey-dew); color: var(--medium-sea-green); margin: 20px 0;">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Уведомления -->
        <h3 class="h3 section-title">Уведомления</h3>
        <?php if (count($notifications) > 0): ?>
            <ul class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <li class="notification-item">
                        <p class="status-message"><?php echo htmlspecialchars($notification['Сообщение']); ?></p>
                        <p class="status-message-date"><?php echo htmlspecialchars($notification['Дата_уведомления']); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="status-message">Уведомлений нет.</p>
        <?php endif; ?>

        <!-- Заявки на просмотр -->
        <h3 class="h3 section-title">Ваши заявки на просмотр</h3>
        <?php if (count($requests) > 0): ?>
            <ul class="request-list">
                <?php foreach ($requests as $request): ?>
                    <li class="request-item">
                        <div class="car-details">
                            <div class="car-image">
                                <figure class="card-banner">
                                    <img src="<?php echo htmlspecialchars($request['Изображение']); ?>" alt="<?php echo htmlspecialchars($request['Марка'] . ' ' . $request['Модель']); ?>" loading="lazy" class="w-100">
                                </figure>
                            </div>
                            <div class="car-info">
                                <h3 class="h3"><?php echo htmlspecialchars($request['Марка'] . ' ' . $request['Модель']); ?></h3>
                                <p class="status-message">Статус: <?php echo htmlspecialchars($request['Статус']); ?></p>
                                <p class="status-message">Дата и время: <?php echo htmlspecialchars($request['Дата_и_время_просмотра']); ?></p>
                                <a href="user_profile.php?cancel_request_id=<?php echo $request['IDЗаявки']; ?>" class="btn" onclick="return confirm('Вы уверены, что хотите отменить заявку?');">Отменить заявку</a>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="status-message">У вас нет активных заявок на просмотр.</p>
        <?php endif; ?>
        <a href="index.php" class="btn" style="margin-top: 20px;">Вернуться на главную</a>
    </div>
</body>
</html>