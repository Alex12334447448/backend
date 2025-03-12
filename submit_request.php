<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] == 3) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Ошибка CSRF: недействительный токен.";
        header("Location: index.php#featured-car");
        exit();
    }

    if (!isset($_POST['car_id']) || !isset($_POST['viewing_date'])) {
        $_SESSION['error_message'] = "Ошибка: все поля формы должны быть заполнены.";
        header("Location: index.php#featured-car");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $car_id = $_POST['car_id'];
    $viewing_date = $_POST['viewing_date'];
    $status_id = 1; // "В пути" (в ожидании одобрения)

    if (!is_numeric($car_id) || !strtotime($viewing_date)) {
        $_SESSION['error_message'] = "Ошибка: некорректные данные.";
        header("Location: index.php#featured-car");
        exit();
    }

    // Проверяем, нет ли уже заявки от этого пользователя на этот автомобиль
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Заявки WHERE IDАвтомобиля = ? AND IDПользователя = ?");
    $stmt_check->execute([$car_id, $user_id]);
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['error_message'] = "Ошибка: вы уже подали заявку на этот автомобиль.";
        header("Location: index.php#featured-car");
        exit();
    }

    // Проверяем пересечение времени с другими заявками
    $stmt_time_check = $pdo->prepare("SELECT COUNT(*) FROM Заявки WHERE IDАвтомобиля = ? AND Дата_и_время_просмотра = ?");
    $stmt_time_check->execute([$car_id, $viewing_date]);
    if ($stmt_time_check->fetchColumn() > 0) {
        $_SESSION['error_message'] = "Ошибка: это время уже занято.";
        header("Location: index.php#featured-car");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Заявки (IDПользователя, IDАвтомобиля, Дата_и_время_просмотра, IDСтатуса) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $car_id, $viewing_date, $status_id]);
        $request_id = $pdo->lastInsertId();
        $_SESSION['success_message'] = "Заявка успешно отправлена на рассмотрение.";
        header("Location: user_profile.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Ошибка при отправке заявки: " . $e->getMessage();
        header("Location: index.php#featured-car");
        exit();
    }
}
?>