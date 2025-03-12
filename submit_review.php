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
        header("Location: index.php#reviews");
        exit();
    }

    if (!isset($_POST['review_text']) || !isset($_POST['rating'])) {
        $_SESSION['error_message'] = "Ошибка: все поля формы должны быть заполнены.";
        header("Location: index.php#reviews");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $review_text = $_POST['review_text'];
    $rating = $_POST['rating'];

    if (strlen($review_text) < 5 || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        $_SESSION['error_message'] = "Ошибка: отзыв должен быть не короче 5 символов, рейтинг — от 1 до 5.";
        header("Location: index.php#reviews");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Отзывы (IDПользователя, Текст_отзыва, Рейтинг) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $review_text, $rating]);
        $_SESSION['success_message'] = "Отзыв успешно отправлен!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Ошибка при отправке отзыва: " . $e->getMessage();
    }

    header("Location: index.php#reviews");
    exit();
}
?>