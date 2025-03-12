<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Пользователи WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Пароль'])) {
        $_SESSION['user_id'] = $user['IDПользователя'];
        $_SESSION['user_name'] = $user['Имя'];
        $_SESSION['user_role'] = $user['IDРоли'];
        header("Location: index.php");
        exit();
    } else {
        echo "Неверный email или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход - ZhukAvto</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2 class="h2 section-title">Вход</h2>
        <form method="POST">
            <div class="input-wrapper">
                <label for="email" class="input-label">Email</label>
                <input type="email" name="email" id="email" class="input-field" placeholder="Введите email" required>
            </div>
            <div class="input-wrapper">
                <label for="password" class="input-label">Пароль</label>
                <input type="password" name="password" id="password" class="input-field" placeholder="Введите пароль" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>
        <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a>.</p>
    </div>
</body>
</html>