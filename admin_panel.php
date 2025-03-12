<?php
session_start();
include 'db_connect.php';
require_once 'lib/tcpdf/tcpdf.php'; // Подключаем TCPDF напрямую

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Обновление статуса заявки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_request'])) {
    $request_id = $_POST['request_id'];
    $new_status_id = $_POST['status_id'];

    $stmt_request = $pdo->prepare("SELECT Заявки.*, Автомобили.Марка, Автомобили.Модель, Пользователи.Имя, Пользователи.Фамилия 
                                   FROM Заявки 
                                   JOIN Автомобили ON Заявки.IDАвтомобиля = Автомобили.IDАвтомобиля 
                                   JOIN Пользователи ON Заявки.IDПользователя = Пользователи.IDПользователя 
                                   WHERE IDЗаявки = ?");
    $stmt_request->execute([$request_id]);
    $request_info = $stmt_request->fetch(PDO::FETCH_ASSOC);
    $user_id = $request_info['IDПользователя'];
    $car_name = $request_info['Марка'] . ' ' . $request_info['Модель'];
    $viewing_date = $request_info['Дата_и_время_просмотра'];

    $stmt_update = $pdo->prepare("UPDATE Заявки SET IDСтатуса = ? WHERE IDЗаявки = ?");
    $stmt_update->execute([$new_status_id, $request_id]);

    $status_name = $new_status_id == 1 ? "В пути" : ($new_status_id == 2 ? "Недоступен" : "Доступен");
    $message = "Статус вашей заявки на просмотр автомобиля $car_name на $viewing_date изменён на: $status_name.";
    $stmt_notify = $pdo->prepare("INSERT INTO Уведомления (IDПользователя, Сообщение) VALUES (?, ?)");
    $stmt_notify->execute([$user_id, $message]);

    $_SESSION['success_message'] = "Статус заявки успешно обновлён, уведомление отправлено.";
    header("Location: admin_panel.php");
    exit();
}

// Добавление автомобиля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_car'])) {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $seats = $_POST['seats'];
    $engine = $_POST['engine'];
    $fuel = $_POST['fuel'];
    $transmission = $_POST['transmission'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    $stmt_add = $pdo->prepare("INSERT INTO Автомобили (Марка, Модель, Год_выпуска, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Цена, Изображение) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_add->execute([$brand, $model, $year, $seats, $engine, $fuel, $transmission, $price, $image]);
    $_SESSION['success_message'] = "Автомобиль успешно добавлен.";
    header("Location: admin_panel.php");
    exit();
}

// Удаление автомобиля
if (isset($_GET['delete_car_id'])) {
    $car_id = $_GET['delete_car_id'];
    $stmt_delete = $pdo->prepare("DELETE FROM Автомобили WHERE IDАвтомобиля = ?");
    $stmt_delete->execute([$car_id]);
    $_SESSION['success_message'] = "Автомобиль успешно удалён.";
    header("Location: admin_panel.php");
    exit();
}

// Обновление данных автомобиля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_car'])) {
    $car_id = $_POST['car_id'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $seats = $_POST['seats'];
    $engine = $_POST['engine'];
    $fuel = $_POST['fuel'];
    $transmission = $_POST['transmission'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    $stmt_update = $pdo->prepare("UPDATE Автомобили SET Марка = ?, Модель = ?, Год_выпуска = ?, Количество_мест = ?, Тип_двигателя = ?, Расход_топлива = ?, Трансмиссия = ?, Цена = ?, Изображение = ? 
                                  WHERE IDАвтомобиля = ?");
    $stmt_update->execute([$brand, $model, $year, $seats, $engine, $fuel, $transmission, $price, $image, $car_id]);
    $_SESSION['success_message'] = "Данные автомобиля успешно обновлены.";
    header("Location: admin_panel.php");
    exit();
}

// Ответ на отзыв
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_review'])) {
    $review_id = $_POST['review_id'];
    $reply_text = $_POST['reply_text'];
    $stmt_reply = $pdo->prepare("INSERT INTO Ответы_на_отзывы (IDОтзыва, IDАдминистратора, Текст_ответа) VALUES (?, ?, ?)");
    $stmt_reply->execute([$review_id, $admin_id, $reply_text]);
    $_SESSION['success_message'] = "Ответ на отзыв успешно отправлен.";
    header("Location: admin_panel.php");
    exit();
}

// Поиск автомобилей
$search_conditions = [];
$search_params = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search_car'])) {
    $brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
    $model = isset($_GET['model']) ? trim($_GET['model']) : '';
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';
    $year = isset($_GET['year']) ? intval($_GET['year']) : '';

    if ($brand) {
        $search_conditions[] = "Марка LIKE ?";
        $search_params[] = '%' . $brand . '%';
    }
    if ($model) {
        $search_conditions[] = "Модель LIKE ?";
        $search_params[] = '%' . $model . '%';
    }
    if ($min_price) {
        $search_conditions[] = "Цена >= ?";
        $search_params[] = $min_price;
    }
    if ($max_price) {
        $search_conditions[] = "Цена <= ?";
        $search_params[] = $max_price;
    }
    if ($year) {
        $search_conditions[] = "Год_выпуска = ?";
        $search_params[] = $year;
    }
}

$search_query = "SELECT * FROM Автомобили";
if (!empty($search_conditions)) {
    $search_query .= " WHERE " . implode(" AND ", $search_conditions);
}
$stmt_cars = $pdo->prepare($search_query);
$stmt_cars->execute($search_params);
$cars = $stmt_cars->fetchAll(PDO::FETCH_ASSOC);

// Экспорт данных автомобилей в PDF
if (isset($_GET['export_cars_pdf'])) {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('ZhukAvto Admin');
    $pdf->SetTitle('Список автомобилей');
    $pdf->SetSubject('Экспорт данных автомобилей');
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 10);

    // Заголовок
    $html = '<h1 style="text-align: center; color: #333;">Список автомобилей</h1>';
    $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
    $html .= '<tr style="background-color: #f2f2f2;">';
    $html .= '<th style="font-weight: bold;">ID</th>';
    $html .= '<th style="font-weight: bold;">Марка</th>';
    $html .= '<th style="font-weight: bold;">Модель</th>';
    $html .= '<th style="font-weight: bold;">Год</th>';
    $html .= '<th style="font-weight: bold;">Места</th>';
    $html .= '<th style="font-weight: bold;">Двигатель</th>';
    $html .= '<th style="font-weight: bold;">Расход (л)</th>';
    $html .= '<th style="font-weight: bold;">Трансмиссия</th>';
    $html .= '<th style="font-weight: bold;">Цена</th>';
    $html .= '</tr>';

    foreach ($cars as $car) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($car['IDАвтомобиля']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Марка']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Модель']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Год_выпуска']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Количество_мест']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Тип_двигателя']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Расход_топлива']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Трансмиссия']) . '</td>';
        $html .= '<td>' . htmlspecialchars($car['Цена']) . '$</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('cars_list.pdf', 'D');
    exit();
}

// Получаем все заявки
$stmt_requests = $pdo->query("SELECT Заявки.*, Статусы.Имя AS Статус, Автомобили.Марка, Автомобили.Модель, Пользователи.Имя AS ИмяПользователя, Пользователи.Фамилия
                             FROM Заявки
                             JOIN Статусы ON Заявки.IDСтатуса = Статусы.IDСтатуса
                             JOIN Автомобили ON Заявки.IDАвтомобиля = Автомобили.IDАвтомобиля
                             JOIN Пользователи ON Заявки.IDПользователя = Пользователи.IDПользователя");
$requests = $stmt_requests->fetchAll(PDO::FETCH_ASSOC);

// Экспорт заявок в PDF
if (isset($_GET['export_requests_pdf'])) {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('ZhukAvto Admin');
    $pdf->SetTitle('Список заявок');
    $pdf->SetSubject('Экспорт данных заявок');
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 10);

    // Заголовок
    $html = '<h1 style="text-align: center; color: #333;">Список заявок</h1>';
    $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
    $html .= '<tr style="background-color: #f2f2f2;">';
    $html .= '<th style="font-weight: bold;">Пользователь</th>';
    $html .= '<th style="font-weight: bold;">Автомобиль</th>';
    $html .= '<th style="font-weight: bold;">Дата и время</th>';
    $html .= '<th style="font-weight: bold;">Статус</th>';
    $html .= '</tr>';

    foreach ($requests as $request) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($request['ИмяПользователя'] . ' ' . $request['Фамилия']) . '</td>';
        $html .= '<td>' . htmlspecialchars($request['Марка'] . ' ' . $request['Модель']) . '</td>';
        $html .= '<td>' . htmlspecialchars($request['Дата_и_время_просмотра']) . '</td>';
        $html .= '<td>' . htmlspecialchars($request['Статус']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('requests_list.pdf', 'D');
    exit();
}

// Получаем все отзывы
$stmt_reviews = $pdo->query("SELECT Отзывы.*, Пользователи.Имя, Пользователи.Фамилия, Ответы_на_отзывы.Текст_ответа, Ответы_на_отзывы.Дата_ответа 
                             FROM Отзывы 
                             JOIN Пользователи ON Отзывы.IDПользователя = Пользователи.IDПользователя 
                             LEFT JOIN Ответы_на_отзывы ON Отзывы.IDОтзыва = Ответы_на_отзывы.IDОтзыва");
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

// Экспорт отзывов в PDF
if (isset($_GET['export_reviews_pdf'])) {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('ZhukAvto Admin');
    $pdf->SetTitle('Список отзывов');
    $pdf->SetSubject('Экспорт данных отзывов');
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 10);

    // Заголовок
    $html = '<h1 style="text-align: center; color: #333;">Список отзывов</h1>';
    $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
    $html .= '<tr style="background-color: #f2f2f2;">';
    $html .= '<th style="font-weight: bold;">Пользователь</th>';
    $html .= '<th style="font-weight: bold;">Отзыв</th>';
    $html .= '<th style="font-weight: bold;">Рейтинг</th>';
    $html .= '<th style="font-weight: bold;">Ответ администратора</th>';
    $html .= '<th style="font-weight: bold;">Дата ответа</th>';
    $html .= '</tr>';

    foreach ($reviews as $review) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($review['Имя'] . ' ' . $review['Фамилия']) . '</td>';
        $html .= '<td>' . htmlspecialchars($review['Текст_отзыва']) . '</td>';
        $html .= '<td>' . htmlspecialchars($review['Рейтинг']) . '/5</td>';
        $html .= '<td>' . ($review['Текст_ответа'] ? htmlspecialchars($review['Текст_ответа']) : 'Нет ответа') . '</td>';
        $html .= '<td>' . ($review['Дата_ответа'] ? htmlspecialchars($review['Дата_ответа']) : '-') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('reviews_list.pdf', 'D');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - ZhukAvto</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2 class="h2 section-title">Админ-панель</h2>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div style="text-align: center; padding: 10px; background: var(--honey-dew); color: var(--medium-sea-green); margin: 20px 0;">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Управление заявками -->
        <h3 class="h3 section-title">Управление заявками <a href="admin_panel.php?export_requests_pdf=1" class="btn">Экспорт в PDF</a></h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px;">Пользователь</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Автомобиль</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Дата и время</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Статус</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($request['ИмяПользователя'] . ' ' . $request['Фамилия']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($request['Марка'] . ' ' . $request['Модель']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($request['Дата_и_время_просмотра']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($request['Статус']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <form action="admin_panel.php" method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $request['IDЗаявки']; ?>">
                                <select name="status_id" class="input-field">
                                    <?php
                                    $stmt_statuses = $pdo->query("SELECT * FROM Статусы");
                                    while ($status = $stmt_statuses->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = $status['IDСтатуса'] == $request['IDСтатуса'] ? 'selected' : '';
                                        echo '<option value="' . $status['IDСтатуса'] . '" ' . $selected . '>' . htmlspecialchars($status['Имя']) . '</option>';
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="update_request" class="btn">Обновить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Добавление автомобиля -->
        <h3 class="h3 section-title">Добавить автомобиль</h3>
        <form action="admin_panel.php" method="POST" class="review-form">
            <div class="input-wrapper">
                <label for="brand" class="input-label">Марка</label>
                <input type="text" name="brand" id="brand" class="input-field" placeholder="Введите марку" required>
            </div>
            <div class="input-wrapper">
                <label for="model" class="input-label">Модель</label>
                <input type="text" name="model" id="model" class="input-field" placeholder="Введите модель" required>
            </div>
            <div class="input-wrapper">
                <label for="year" class="input-label">Год выпуска</label>
                <input type="number" name="year" id="year" class="input-field" placeholder="Введите год выпуска" required>
            </div>
            <div class="input-wrapper">
                <label for="seats" class="input-label">Количество мест</label>
                <input type="number" name="seats" id="seats" class="input-field" placeholder="Введите количество мест" required>
            </div>
            <div class="input-wrapper">
                <label for="engine" class="input-label">Тип двигателя</label>
                <input type="text" name="engine" id="engine" class="input-field" placeholder="Введите тип двигателя" required>
            </div>
            <div class="input-wrapper">
                <label for="fuel" class="input-label">Расход топлива (л)</label>
                <input type="number" step="0.1" name="fuel" id="fuel" class="input-field" placeholder="Введите расход топлива" required>
            </div>
            <div class="input-wrapper">
                <label for="transmission" class="input-label">Трансмиссия</label>
                <input type="text" name="transmission" id="transmission" class="input-field" placeholder="Введите тип трансмиссии" required>
            </div>
            <div class="input-wrapper">
                <label for="price" class="input-label">Цена ($)</label>
                <input type="number" name="price" id="price" class="input-field" placeholder="Введите цену" required>
            </div>
            <div class="input-wrapper">
                <label for="image" class="input-label">URL изображения</label>
                <input type="text" name="image" id="image" class="input-field" placeholder="Введите URL изображения" required>
            </div>
            <button type="submit" name="add_car" class="btn">Добавить автомобиль</button>
        </form>

        <!-- Поиск автомобилей -->
        <h3 class="h3 section-title">Поиск автомобилей</h3>
        <form action="admin_panel.php" method="GET" class="review-form">
            <div class="input-wrapper">
                <label for="brand" class="input-label">Марка</label>
                <input type="text" name="brand" id="brand" class="input-field" placeholder="Введите марку" value="<?php echo isset($_GET['brand']) ? htmlspecialchars($_GET['brand']) : ''; ?>">
            </div>
            <div class="input-wrapper">
                <label for="model" class="input-label">Модель</label>
                <input type="text" name="model" id="model" class="input-field" placeholder="Введите модель" value="<?php echo isset($_GET['model']) ? htmlspecialchars($_GET['model']) : ''; ?>">
            </div>
            <div class="input-wrapper">
                <label for="min_price" class="input-label">Минимальная цена ($)</label>
                <input type="number" name="min_price" id="min_price" class="input-field" placeholder="Введите минимальную цену" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
            </div>
            <div class="input-wrapper">
                <label for="max_price" class="input-label">Максимальная цена ($)</label>
                <input type="number" name="max_price" id="max_price" class="input-field" placeholder="Введите максимальную цену" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
            </div>
            <div class="input-wrapper">
                <label for="year" class="input-label">Год выпуска</label>
                <input type="number" name="year" id="year" class="input-field" placeholder="Введите год выпуска" value="<?php echo isset($_GET['year']) ? htmlspecialchars($_GET['year']) : ''; ?>">
            </div>
            <button type="submit" name="search_car" class="btn">Поиск</button>
        </form>

        <!-- Список автомобилей -->
        <h3 class="h3 section-title">Список автомобилей <a href="admin_panel.php?export_cars_pdf=1" class="btn">Экспорт в PDF</a></h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px;">ID</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Марка</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Модель</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Год</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Цена</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $car['IDАвтомобиля']; ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($car['Марка']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($car['Модель']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($car['Год_выпуска']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($car['Цена']); ?>$</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <a href="admin_panel.php?edit_car_id=<?php echo $car['IDАвтомобиля']; ?>" class="btn">Изменить</a>
                            <a href="admin_panel.php?delete_car_id=<?php echo $car['IDАвтомобиля']; ?>" class="btn" onclick="return confirm('Вы уверены, что хотите удалить автомобиль?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Форма редактирования автомобиля -->
        <?php if (isset($_GET['edit_car_id'])): ?>
            <?php
            $edit_car_id = $_GET['edit_car_id'];
            $stmt_edit_car = $pdo->prepare("SELECT * FROM Автомобили WHERE IDАвтомобиля = ?");
            $stmt_edit_car->execute([$edit_car_id]);
            $edit_car = $stmt_edit_car->fetch(PDO::FETCH_ASSOC);
            ?>
            <h3 class="h3 section-title">Изменить автомобиль</h3>
            <form action="admin_panel.php" method="POST" class="review-form">
                <input type="hidden" name="car_id" value="<?php echo $edit_car['IDАвтомобиля']; ?>">
                <div class="input-wrapper">
                    <label for="brand" class="input-label">Марка</label>
                    <input type="text" name="brand" id="brand" class="input-field" value="<?php echo htmlspecialchars($edit_car['Марка']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="model" class="input-label">Модель</label>
                    <input type="text" name="model" id="model" class="input-field" value="<?php echo htmlspecialchars($edit_car['Модель']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="year" class="input-label">Год выпуска</label>
                    <input type="number" name="year" id="year" class="input-field" value="<?php echo htmlspecialchars($edit_car['Год_выпуска']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="seats" class="input-label">Количество мест</label>
                    <input type="number" name="seats" id="seats" class="input-field" value="<?php echo htmlspecialchars($edit_car['Количество_мест']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="engine" class="input-label">Тип двигателя</label>
                    <input type="text" name="engine" id="engine" class="input-field" value="<?php echo htmlspecialchars($edit_car['Тип_двигателя']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="fuel" class="input-label">Расход топлива (л)</label>
                    <input type="number" step="0.1" name="fuel" id="fuel" class="input-field" value="<?php echo htmlspecialchars($edit_car['Расход_топлива']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="transmission" class="input-label">Трансмиссия</label>
                    <input type="text" name="transmission" id="transmission" class="input-field" value="<?php echo htmlspecialchars($edit_car['Трансмиссия']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="price" class="input-label">Цена ($)</label>
                    <input type="number" name="price" id="price" class="input-field" value="<?php echo htmlspecialchars($edit_car['Цена']); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="image" class="input-label">URL изображения</label>
                    <input type="text" name="image" id="image" class="input-field" value="<?php echo htmlspecialchars($edit_car['Изображение']); ?>" required>
                </div>
                <button type="submit" name="update_car" class="btn">Сохранить изменения</button>
                <a href="admin_panel.php" class="btn">Отмена</a>
            </form>
        <?php endif; ?>

        <!-- Ответы на отзывы -->
        <h3 class="h3 section-title">Ответы на отзывы <a href="admin_panel.php?export_reviews_pdf=1" class="btn">Экспорт в PDF</a></h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px;">Пользователь</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Отзыв</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Рейтинг</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Ответ</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($review['Имя'] . ' ' . $review['Фамилия']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($review['Текст_отзыва']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($review['Рейтинг']); ?>/5</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <?php if ($review['Текст_ответа']): ?>
                                <?php echo htmlspecialchars($review['Текст_ответа']); ?><br>
                                <small><?php echo htmlspecialchars($review['Дата_ответа']); ?></small>
                            <?php else: ?>
                                Нет ответа
                            <?php endif; ?>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <form action="admin_panel.php" method="POST" class="review-form">
                                <input type="hidden" name="review_id" value="<?php echo $review['IDОтзыва']; ?>">
                                <textarea name="reply_text" class="input-field" placeholder="Введите ответ" required></textarea>
                                <button type="submit" name="reply_review" class="btn">Ответить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="index.php" class="btn" style="margin-top: 20px;">Вернуться на главную</a>
    </div>
</body>
</html>