<?php
session_start();
require 'db_connect.php';

// Создаём CSRF-токен
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Логика поиска
$search_result = null;
$search_message = '';

if (!isset($pdo)) {
    $search_message = "Ошибка: не удалось подключиться к базе данных.";
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $car_model = isset($_GET['car-model']) ? trim($_GET['car-model']) : '';
        $max_price = isset($_GET['max-price']) ? trim($_GET['max-price']) : '';
        $year = isset($_GET['year']) ? trim($_GET['year']) : '';

        if ($car_model !== '' && $max_price !== '' && $year !== '' && is_numeric($max_price) && is_numeric($year)) {
            $max_price = floatval($max_price);
            $year = intval($year);

            if ($max_price > 0 && $year >= 1900 && $year <= date('Y')) {
                $query = "SELECT * FROM Автомобили WHERE (Марка LIKE ? OR Модель LIKE ?) AND Цена <= ? AND Год_выпуска = ? LIMIT 1";
                $stmt = $pdo->prepare($query);
                $like_car_model = '%' . $car_model . '%';
                $stmt->execute([$like_car_model, $like_car_model, $max_price, $year]);
                $search_result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$search_result) {
                    $search_message = "Автомобиль не найден. Попробуйте изменить параметры поиска.";
                }
            } else {
                $search_message = "Пожалуйста, введите корректные значения: цена должна быть больше 0, год выпуска — между 1900 и текущим годом.";
            }
        } elseif (!empty($_GET['car-model']) || !empty($_GET['max-price']) || !empty($_GET['year'])) {
            $search_message = "Пожалуйста, заполните все поля для поиска.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ZhukAvto - Автомобили из Европы и США</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <?php if (isset($_SESSION['success_message'])): ?>
    <div style="text-align: center; padding: 10px; background: var(--honey-dew); color: var(--medium-sea-green); margin: 20px 0;">
      <?php echo htmlspecialchars($_SESSION['success_message']); ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_message'])): ?>
    <div style="text-align: center; padding: 10px; background: var(--lavender-blush); color: var(--red-salsa); margin: 20px 0;">
      <?php echo htmlspecialchars($_SESSION['error_message']); ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
  <?php endif; ?>

  <header class="header" data-header>
    <div class="container">
      <div class="overlay" data-overlay></div>
      <a href="#" class="logo">
        <img src="./assets/images/logo.svg" alt="ZhukAvto">
      </a>
      <nav class="navbar" data-navbar>
        <ul class="navbar-list">
          <li><a href="#home" class="navbar-link" data-nav-link>Главная</a></li>
          <li><a href="#featured-car" class="navbar-link" data-nav-link>Доступные автомобили</a></li>
          <li><a href="#reviews" class="navbar-link" data-nav-link>Отзывы</a></li>
          <li><a href="#blog" class="navbar-link" data-nav-link>Блог</a></li>
        </ul>
      </nav>
      <div class="header-actions">
        <div class="header-contact">
          <a href="tel:88002345678" class="contact-link">+375292357791</a>
        </div>
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])): ?>
          <span>Привет, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
          <?php if ($_SESSION['user_role'] == 1): ?>
            <a href="admin_panel.php" class="btn">Админ-панель</a>
          <?php endif; ?>
          <?php if ($_SESSION['user_role'] != 3): ?>
            <a href="user_profile.php" class="btn">Личный кабинет</a>
          <?php endif; ?>
          <a href="logout.php" class="btn user-btn" aria-label="Logout">
            <ion-icon name="log-out-outline"></ion-icon>
          </a>
        <?php else: ?>
          <a href="login.php" class="btn user-btn" aria-label="Login">
            <ion-icon name="person-outline"></ion-icon>
          </a>
        <?php endif; ?>
        <button class="nav-toggle-btn" data-nav-toggle-btn aria-label="Toggle Menu">
          <span class="one"></span>
          <span class="two"></span>
          <span class="three"></span>
        </button>
      </div>
    </div>
  </header>

  <main>
    <article>
      <section class="section hero" id="home">
        <div class="container">
          <div class="hero-content">
            <h2 class="h1 hero-title">Автомобили из Европы и США в наличии и под заказ</h2>
            <p class="hero-text">ZhukAvto оказывает всестороннюю поддержку на любом этапе приобретения авто</p>
          </div>
          <div class="hero-banner"></div>
          <form action="index.php" method="GET" class="hero-form" aria-label="Форма поиска автомобилей">
            <div class="input-wrapper">
              <label for="input-1" class="input-label">Марка авто</label>
              <input type="text" name="car-model" id="input-1" class="input-field" placeholder="Какую машину вы ищете?" value="<?php echo isset($_GET['car-model']) ? htmlspecialchars($_GET['car-model']) : ''; ?>" aria-label="Марка или модель автомобиля">
            </div>
            <div class="input-wrapper">
              <label for="input-2" class="input-label">Максимальная цена</label>
              <input type="text" name="max-price" id="input-2" class="input-field" placeholder="Добавьте сумму в $" value="<?php echo isset($_GET['max-price']) ? htmlspecialchars($_GET['max-price']) : ''; ?>" aria-label="Максимальная цена в долларах">
            </div>
            <div class="input-wrapper">
              <label for="input-3" class="input-label">Год</label>
              <input type="text" name="year" id="input-3" class="input-field" placeholder="Добавьте год выпуска" value="<?php echo isset($_GET['year']) ? htmlspecialchars($_GET['year']) : ''; ?>" aria-label="Год выпуска автомобиля">
            </div>
            <button type="submit" class="btn" aria-label="Найти автомобиль">Поиск</button>
          </form>
          <?php if (isset($_GET['car-model']) && isset($_GET['max-price']) && isset($_GET['year'])): ?>
            <div class="search-result">
              <?php if ($search_result): ?>
                <h3 class="h3">Результат поиска</h3>
                <div class="search-result-card">
                  <div class="search-result-image">
                    <figure class="card-banner">
                      <img src="<?php echo htmlspecialchars($search_result['Изображение']); ?>" alt="<?php echo htmlspecialchars($search_result['Марка'] . ' ' . $search_result['Модель']); ?>" loading="lazy" class="w-100">
                    </figure>
                  </div>
                  <div class="search-result-content">
                    <div class="card-title-wrapper">
                      <h3 class="h3 card-title"><a href="#"><?php echo htmlspecialchars($search_result['Марка'] . ' ' . $search_result['Модель']); ?></a></h3>
                      <data class="year" value="<?php echo htmlspecialchars($search_result['Год_выпуска']); ?>"><?php echo htmlspecialchars($search_result['Год_выпуска']); ?></data>
                    </div>
                    <div class="card-details">
                      <ul class="card-list">
                        <li class="card-list-item"><ion-icon name="people-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($search_result['Количество_мест']); ?> людей</span></li>
                        <li class="card-list-item"><ion-icon name="flash-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($search_result['Тип_двигателя']); ?></span></li>
                        <li class="card-list-item"><ion-icon name="speedometer-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($search_result['Расход_топлива']); ?> л</span></li>
                        <li class="card-list-item"><ion-icon name="hardware-chip-outline"></ion-icon><span class="card-item-text"><?php echo htmlspecialchars($search_result['Трансмиссия']); ?></span></li>
                      </ul>
                      <div class="card-price-wrapper">
                        <p class="card-price"><strong><?php echo htmlspecialchars($search_result['Цена']); ?>$</strong></p>
                        <button class="btn fav-btn" aria-label="Add to favourite list"><ion-icon name="heart-outline"></ion-icon></button>
                        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] != 3): ?>
                          <?php
                          $stmt_check = $pdo->prepare("SELECT Заявки.*, Статусы.Имя AS Статус 
                                                       FROM Заявки 
                                                       JOIN Статусы ON Заявки.IDСтатуса = Статусы.IDСтатуса 
                                                       WHERE Заявки.IDАвтомобиля = ? 
                                                       AND Заявки.IDПользователя = ? 
                                                       ORDER BY Заявки.Дата_и_время_просмотра DESC LIMIT 1");
                          $stmt_check->execute([$search_result['IDАвтомобиля'], $_SESSION['user_id']]);
                          $existing_request = $stmt_check->fetch(PDO::FETCH_ASSOC);

                          if ($existing_request) {
                            echo '<a href="user_profile.php" class="btn">' . htmlspecialchars($existing_request['Статус']) . '</a>';
                          } else {
                            $stmt_time_check = $pdo->prepare("SELECT COUNT(*) FROM Заявки WHERE IDАвтомобиля = ? AND Дата_и_время_просмотра > NOW() AND IDСтатуса = 3");
                            $stmt_time_check->execute([$search_result['IDАвтомобиля']]);
                            $has_conflicting_requests = $stmt_time_check->fetchColumn() > 0;

                            if ($has_conflicting_requests) {
                              echo '<a href="view_request_status.php?car_id=' . $search_result['IDАвтомобиля'] . '" class="btn">Занято</a>';
                            } else {
                              echo '<a href="view_request_status.php?car_id=' . $search_result['IDАвтомобиля'] . '" class="btn">Записаться на просмотр</a>';
                            }
                          }
                          ?>
                        <?php else: ?>
                          <a href="login.php" class="btn">Записаться на просмотр</a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <p class="search-message"><?php echo htmlspecialchars($search_message); ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
      <section class="section featured-car" id="featured-car">
        <div class="container">
          <div class="title-wrapper">
            <h2 class="h2 section-title">Доступные автомобили</h2>
          </div>
          <ul class="featured-car-list">
            <?php
            if (!isset($pdo)) {
              echo '<li><p style="text-align: center; color: var(--independence);">Ошибка подключения к базе данных.</p></li>';
            } else {
              try {
                $stmt = $pdo->query("SELECT * FROM Автомобили");
                $delay = 1;
                if ($stmt->rowCount() == 0) {
                  echo '<li><p style="text-align: center; color: var(--independence);">Автомобили отсутствуют.</p></li>';
                }
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  echo '<li>';
                  echo '<div class="featured-car-card" style="--delay: ' . $delay . ';">';
                  echo '<figure class="card-banner">';
                  $image = file_exists($row['Изображение']) ? htmlspecialchars($row['Изображение']) : './assets/images/default-car.jpg';
                  echo '<img src="' . $image . '" alt="' . htmlspecialchars($row['Марка']) . ' ' . htmlspecialchars($row['Модель']) . '" loading="lazy" width="440" height="300" class="w-100">';
                  echo '</figure>';
                  echo '<div class="card-content">';
                  echo '<div class="card-title-wrapper">';
                  echo '<h3 class="h3 card-title"><a href="#">' . htmlspecialchars($row['Марка']) . ' ' . htmlspecialchars($row['Модель']) . '</a></h3>';
                  echo '<data class="year" value="' . htmlspecialchars($row['Год_выпуска']) . '">' . htmlspecialchars($row['Год_выпуска']) . '</data>';
                  echo '</div>';
                  echo '<ul class="card-list">';
                  echo '<li class="card-list-item"><ion-icon name="people-outline"></ion-icon><span class="card-item-text">' . htmlspecialchars($row['Количество_мест']) . ' людей</span></li>';
                  echo '<li class="card-list-item"><ion-icon name="flash-outline"></ion-icon><span class="card-item-text">' . htmlspecialchars($row['Тип_двигателя']) . '</span></li>';
                  echo '<li class="card-list-item"><ion-icon name="speedometer-outline"></ion-icon><span class="card-item-text">' . htmlspecialchars($row['Расход_топлива']) . ' л</span></li>';
                  echo '<li class="card-list-item"><ion-icon name="hardware-chip-outline"></ion-icon><span class="card-item-text">' . htmlspecialchars($row['Трансмиссия']) . '</span></li>';
                  echo '</ul>';
                  echo '<div class="card-price-wrapper">';
                  echo '<p class="card-price"><strong>' . htmlspecialchars($row['Цена']) . '$</strong></p>';
                  echo '<button class="btn fav-btn" aria-label="Add to favourite list"><ion-icon name="heart-outline"></ion-icon></button>';
                  if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] != 3) {
                    $stmt_check = $pdo->prepare("SELECT Заявки.*, Статусы.Имя AS Статус 
                                                 FROM Заявки 
                                                 JOIN Статусы ON Заявки.IDСтатуса = Статусы.IDСтатуса 
                                                 WHERE Заявки.IDАвтомобиля = ? 
                                                 AND Заявки.IDПользователя = ? 
                                                 ORDER BY Заявки.Дата_и_время_просмотра DESC LIMIT 1");
                    $stmt_check->execute([$row['IDАвтомобиля'], $_SESSION['user_id']]);
                    $existing_request = $stmt_check->fetch(PDO::FETCH_ASSOC);

                    if ($existing_request) {
                      echo '<a href="user_profile.php" class="btn">' . htmlspecialchars($existing_request['Статус']) . '</a>';
                    } else {
                      $stmt_time_check = $pdo->prepare("SELECT COUNT(*) FROM Заявки WHERE IDАвтомобиля = ? AND Дата_и_время_просмотра > NOW() AND IDСтатуса = 3");
                      $stmt_time_check->execute([$row['IDАвтомобиля']]);
                      $has_conflicting_requests = $stmt_time_check->fetchColumn() > 0;

                      if ($has_conflicting_requests) {
                        echo '<a href="view_request_status.php?car_id=' . $row['IDАвтомобиля'] . '" class="btn">Занято</a>';
                      } else {
                        echo '<a href="view_request_status.php?car_id=' . $row['IDАвтомобиля'] . '" class="btn">Записаться на просмотр</a>';
                      }
                    }
                  } else {
                    echo '<a href="login.php" class="btn">Записаться на просмотр</a>';
                  }
                  echo '</div>';
                  echo '</div>';
                  echo '</div>';
                  echo '</li>';
                  $delay++;
                }
              } catch (PDOException $e) {
                echo '<li><p style="text-align: center; color: var(--independence);">Ошибка загрузки автомобилей: ' . htmlspecialchars($e->getMessage()) . '</p></li>';
              }
            }
            ?>
          </ul>
        </div>
      </section>
      <section class="section get-start" id="reviews">
        <div class="container">
          <h2 class="h2 section-title">Отзывы наших клиентов</h2>
          <ul class="get-start-list">
            <?php
            if (!isset($pdo)) {
              echo '<li><p style="text-align: center; color: var(--independence);">Ошибка подключения к базе данных.</p></li>';
            } else {
              try {
                $stmt = $pdo->query("SELECT Отзывы.*, Пользователи.Имя, Пользователи.Фамилия, Ответы_на_отзывы.Текст_ответа, Ответы_на_отзывы.Дата_ответа 
                                     FROM Отзывы 
                                     JOIN Пользователи ON Отзывы.IDПользователя = Пользователи.IDПользователя 
                                     LEFT JOIN Ответы_на_отзывы ON Отзывы.IDОтзыва = Ответы_на_отзывы.IDОтзыва");
                if ($stmt->rowCount() == 0) {
                  echo '<li><p style="text-align: center; color: var(--independence);">Отзывы отсутствуют.</p></li>';
                }
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  echo '<li>';
                  echo '<div class="get-start-card">';
                  echo '<h3 class="card-title">' . htmlspecialchars($row['Имя']) . ' ' . htmlspecialchars($row['Фамилия']) . '</h3>';
                  echo '<p class="card-text">' . htmlspecialchars($row['Текст_отзыва']) . '</p>';
                  echo '<p class="card-text">Рейтинг: ' . htmlspecialchars($row['Рейтинг']) . '/5</p>';
                  if ($row['Текст_ответа']) {
                    echo '<p class="card-text"><strong>Ответ администратора:</strong> ' . htmlspecialchars($row['Текст_ответа']) . '</p>';
                    echo '<p class="card-text"><small>Дата ответа: ' . htmlspecialchars($row['Дата_ответа']) . '</small></p>';
                  }
                  echo '</div>';
                  echo '</li>';
                }
              } catch (PDOException $e) {
                echo '<li><p style="text-align: center; color: var(--independence);">Ошибка загрузки отзывов: ' . htmlspecialchars($e->getMessage()) . '</p></li>';
              }
            }
            ?>
          </ul>
          <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] != 3): ?>
            <h3 class="h3 section-title">Оставить отзыв</h3>
            <form action="submit_review.php" method="POST" class="review-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
              <div class="input-wrapper">
                <label for="review-text" class="input-label">Ваш отзыв</label>
                <textarea name="review_text" id="review-text" class="input-field" placeholder="Напишите ваш отзыв" required></textarea>
              </div>
              <div class="input-wrapper">
                <label for="rating" class="input-label">Рейтинг</label>
                <select name="rating" id="rating" class="input-field" required>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                </select>
              </div>
              <button type="submit" class="btn">Отправить отзыв</button>
            </form>
          <?php else: ?>
            <p>Чтобы оставить отзыв, пожалуйста, <a href="login.php">войдите</a>.</p>
          <?php endif; ?>
        </div>
      </section>

      <section class="section blog" id="blog">
        <div class="container">
          <h2 class="h2 section-title">Блог</h2>
          <ul class="blog-list has-scrollbar">
            <li>
              <div class="blog-card">
                <figure class="card-banner">
                  <a href="#">
                    <img src="./assets/images/blog-1.jpg" alt="Надёжность современных немецких машин: мифы и реальность" loading="lazy" class="w-100">
                  </a>
                  <a href="#" class="btn card-badge">ЧИТАТЬ</a>
                </figure>
                <div class="card-content">
                  <h3 class="h3 card-title"><a href="#">Надёжность современных немецких машин: мифы и реальность</a></h3>
                </div>
              </div>
            </li>
            <li>
              <div class="blog-card">
                <figure class="card-banner">
                  <a href="#">
                    <img src="./assets/images/blog-2.jpg" alt="Покупка автомобиля в Европе: частник или дилер?" loading="lazy" class="w-100">
                  </a>
                  <a href="#" class="btn card-badge">ЧИТАТЬ</a>
                </figure>
                <div class="card-content">
                  <h3 class="h3 card-title"><a href="#">Покупка автомобиля в Европе: частник или дилер?</a></h3>
                </div>
              </div>
            </li>
            <li>
              <div class="blog-card">
                <figure class="card-banner">
                  <a href="#">
                    <img src="./assets/images/blog-3.jpg" alt="Сравнение эксплуатации электрокаров и автомобилей с ДВС" loading="lazy" class="w-100">
                  </a>
                  <a href="#" class="btn card-badge">ЧИТАТЬ</a>
                </figure>
                <div class="card-content">
                  <h3 class="h3 card-title"><a href="#">Сравнение эксплуатации электрокаров и автомобилей с ДВС</a></h3>
                </div>
              </div>
            </li>
            <li>
              <div class="blog-card">
                <figure class="card-banner">
                  <a href="#">
                    <img src="./assets/images/blog-4.jpg" alt="Курс доллара вырос — что будет с ценами на автомобили?" loading="lazy" class="w-100">
                  </a>
                  <a href="#" class="btn card-badge">ЧИТАТЬ</a>
                </figure>
                <div class="card-content">
                  <h3 class="h3 card-title"><a href="#">Курс доллара вырос — что будет с ценами на автомобили?</a></h3>
                </div>
              </div>
            </li>
            <li>
              <div class="blog-card">
                <figure class="card-banner">
                  <a href="#">
                    <img src="./assets/images/blog-5.jpg" alt="Volkswagen решил взяться за качество своих моделей" loading="lazy" class="w-100">
                  </a>
                  <a href="#" class="btn card-badge">ЧИТАТЬ</a>
                </figure>
                <div class="card-content">
                  <h3 class="h3 card-title"><a href="#">Volkswagen решил взяться за качество своих моделей</a></h3>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </section>
    </article>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-top">
        <div class="footer-brand">
          <a href="#" class="logo">
            <img src="./assets/images/logo.svg" alt="Ridex logo">
          </a>
          <p class="footer-text">ООО "Жук Авто"</p>
        </div>
        <ul class="footer-list">
          <li><p class="footer-list-title">+375(33)6617097</p></li>
          <li><a href="#" class="footer-link">+375(29)8882955</a></li>
        </ul>
        <ul class="footer-list">
          <li><p class="footer-list-title">Ежедневно с 9:00 до 21:00</p></li>
          <li><a href="#" class="footer-link">г.Минск, Лошицкая улица, 19</a></li>
        </ul>
      </div>
      <div class="footer-bottom">
        <ul class="social-list">
          <li><a href="#" class="social-link"><ion-icon name="logo-facebook"></ion-icon></a></li>
          <li><a href="#" class="social-link"><ion-icon name="logo-instagram"></ion-icon></a></li>
          <li><a href="#" class="social-link"><ion-icon name="logo-twitter"></ion-icon></a></li>
          <li><a href="#" class="social-link"><ion-icon name="logo-linkedin"></ion-icon></a></li>
          <li><a href="#" class="social-link"><ion-icon name="logo-skype"></ion-icon></a></li>
          <li><a href="#" class="social-link"><ion-icon name="mail-outline"></ion-icon></a></li>
        </ul>
      </div>
    </div>
  </footer>

  <script src="./assets/js/script.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>