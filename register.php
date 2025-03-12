<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = 2; // Клиент по умолчанию

    $stmt = $pdo->prepare("INSERT INTO Пользователи (Имя, Фамилия, Телефон, Email, Пароль, IDРоли) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $surname, $phone, $email, $password, $role_id]);

    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация - ZhukAvto</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2 class="h2 section-title">Регистрация</h2>
        <form method="POST">
            <div class="input-wrapper">
                <label for="name" class="input-label">Имя</label>
                <input type="text" name="name" id="name" class="input-field" placeholder="Введите имя" required>
            </div>
            <div class="input-wrapper">
                <label for="surname" class="input-label">Фамилия</label>
                <input type="text" name="surname" id="surname" class="input-field" placeholder="Введите фамилию" required>
            </div>
            <div class="input-wrapper">
                <label for="phone" class="input-label">Телефон</label>
                <input type="text" name="phone" id="phone" class="input-field" placeholder="Введите телефон">
            </div>
            <div class="input-wrapper">
                <label for="email" class="input-label">Email</label>
                <input type="email" name="email" id="email" class="input-field" placeholder="Введите email" required>
            </div>
            <div class="input-wrapper">
                <label for="password" class="input-label">Пароль</label>
                <input type="password" name="password" id="password" class="input-field" placeholder="Введите пароль" required>
            </div>
            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>
        <p>Уже есть аккаунт? <a href="login.php">Войдите</a>.</p>
    </div>
</body>
</html>