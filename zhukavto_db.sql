CREATE TABLE Роли (
    IDРоли INT AUTO_INCREMENT PRIMARY KEY,
    Название VARCHAR(50) NOT NULL
);
INSERT INTO Роли (Название) VALUES ('Админ'), ('Клиент'), ('Гость');
-- Таблица Пользователи
CREATE TABLE Пользователи (
    IDПользователя INT AUTO_INCREMENT PRIMARY KEY,
    Имя VARCHAR(50) NOT NULL,
    Фамилия VARCHAR(50) NOT NULL,
    Телефон VARCHAR(20),
    Email VARCHAR(100) NOT NULL UNIQUE,
    Пароль VARCHAR(255) NOT NULL,
    IDРоли INT,
    FOREIGN KEY (IDРоли) REFERENCES Роли(IDРоли)
);
INSERT INTO Пользователи (Имя, Фамилия, Телефон, Email, Пароль, IDРоли)
VALUES ('Админ', 'Админов', '+375291234567', 'admin@zhukavto.com', '$2y$10$sHu.mIA2Xe0g8Mm5bEnrn.tEUA4BaUjqKOEVlLe3gm0lyCXge4Ftm', 1);

-- Таблица Автомобили
CREATE TABLE Автомобили (
    IDАвтомобиля INT AUTO_INCREMENT PRIMARY KEY,
    Марка VARCHAR(50) NOT NULL,
    Модель VARCHAR(50) NOT NULL,
    Год_выпуска INT NOT NULL,
    VIN VARCHAR(17) NOT NULL UNIQUE,
    Цена DECIMAL(10, 2) NOT NULL,
    Количество_мест INT NOT NULL,
    Тип_двигателя VARCHAR(50) NOT NULL,
    Расход_топлива DECIMAL(5, 1) NOT NULL,
    Трансмиссия VARCHAR(50) NOT NULL,
    Изображение VARCHAR(255) NOT NULL
);
-- Ford Escape IV, 2021
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Ford', 'Escape IV', 2021, '4T1BF1FK0LU123456', 20950.00, 5, 'Бензин', 7.8, 'Автомат', './assets/images/car-1.jpg');

-- Peugeot 308 T9 · Рестайлинг, 2020
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Peugeot', '308 T9 Рестайлинг', 2020, '5UXKR0C54K0A12345', 11650.00, 5, 'Бензин', 5.3, 'Механика', './assets/images/car-2.jpg');

-- MINI Countryman F60 · Рестайлинг, 2021
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('MINI', 'Countryman F60 Рестайлинг', 2021, '7JTKR0C54K0A12347', 24700.00, 5, 'Бензин', 6.1, 'Автомат', './assets/images/car-3.jpg');

-- Volkswagen Transporter T5, 2004
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Volkswagen', 'Transporter T5', 2004, '8JTKR0C54K0A12348', 11450.00, 8, 'Дизель', 7.6, 'Механика', './assets/images/car-4.jpg');

-- Renault Grand Scenic III · 2-й рестайлинг, 2013
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Renault', 'Grand Scenic III 2-й рестайлинг', 2013, '9JTKR0C54K0A12349', 11950.00, 5, 'Дизель', 4.4, 'Механика', './assets/images/car-5.jpg');

-- Skoda Rapid I · Рестайлинг, 2019
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Skoda', 'Rapid I Рестайлинг', 2019, '1JTKR0C54K0A12350', 15000.00, 5, 'Бензин', 5.9, 'Механика', './assets/images/car-6.jpg');
-- Toyota Corolla, 2022
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Toyota', 'Corolla', 2022, '2T1BURHE0KC123451', 22000.00, 5, 'Бензин', 6.0, 'Автомат', './assets/images/car-7.jpg');

-- Honda Civic, 2021
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Honda', 'Civic', 2021, '2HGFC2F69MH123452', 23000.00, 5, 'Бензин', 5.8, 'Автомат', './assets/images/car-8.jpg');

-- BMW 3 Series, 2020
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('BMW', '3 Series', 2020, 'WBA5R1C05LF123453', 35000.00, 5, 'Бензин', 7.0, 'Автомат', './assets/images/car-9.jpg');

-- Mercedes-Benz C-Class, 2019
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Mercedes-Benz', 'C-Class', 2019, 'WDDWJ4JB0KF123454', 38000.00, 5, 'Бензин', 6.5, 'Автомат', './assets/images/car-10.jpg');

-- Audi A4, 2021
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Audi', 'A4', 2021, 'WAUABAF45MA123455', 39000.00, 5, 'Бензин', 6.8, 'Автомат', './assets/images/car-11.jpg');

-- Volkswagen Golf, 2020
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Volkswagen', 'Golf', 2020, 'WVWZZZAUZLW123456', 20000.00, 5, 'Бензин', 5.5, 'Механика', './assets/images/car-12.jpg');

-- Hyundai Tucson, 2022
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Hyundai', 'Tucson', 2022, 'KM8J3CAL0NU123457', 28000.00, 5, 'Бензин', 6.2, 'Автомат', './assets/images/car-13.jpg');

-- Kia Sportage, 2021
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Kia', 'Sportage', 2021, 'KNDPM3AC0M7123458', 27000.00, 5, 'Бензин', 6.4, 'Автомат', './assets/images/car-14.jpg');

-- Ford Mustang, 2018
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Ford', 'Mustang', 2018, '1FA6P8CF0J5123459', 32000.00, 4, 'Бензин', 8.5, 'Автомат', './assets/images/car-15.jpg');

-- Tesla Model 3, 2023
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Tesla', 'Model 3', 2023, '5YJ3E1EA0PF123460', 45000.00, 5, 'Электрический', 0.0, 'Автомат', './assets/images/car-16.jpg');

-- Nissan Leaf, 2020
INSERT INTO Автомобили (Марка, Модель, Год_выпуска, VIN, Цена, Количество_мест, Тип_двигателя, Расход_топлива, Трансмиссия, Изображение)
VALUES ('Nissan', 'Leaf', 2020, '1N4AZ0CP0LC123461', 25000.00, 5, 'Электрический', 0.0, 'Автомат', './assets/images/car-17.jpg');

-- Таблица Статусы
CREATE TABLE Статусы (
    IDСтатуса INT AUTO_INCREMENT PRIMARY KEY,
    Имя VARCHAR(50) NOT NULL
);
INSERT INTO Статусы (Имя) VALUES ('В пути'), ('Доступен'), ('Недоступен');

-- Таблица Заявки
CREATE TABLE Заявки (
    IDЗаявки INT AUTO_INCREMENT PRIMARY KEY,
    IDПользователя INT,
    IDАвтомобиля INT,
    Дата_и_время_просмотра DATETIME NOT NULL,
    IDСтатуса INT,
    FOREIGN KEY (IDПользователя) REFERENCES Пользователи(IDПользователя),
    FOREIGN KEY (IDАвтомобиля) REFERENCES Автомобили(IDАвтомобиля),
    FOREIGN KEY (IDСтатуса) REFERENCES Статусы(IDСтатуса)
);

-- Таблица Отзывы
CREATE TABLE Отзывы (
    IDОтзыва INT AUTO_INCREMENT PRIMARY KEY,
    IDПользователя INT,
    Текст_отзыва TEXT NOT NULL,
    Рейтинг INT CHECK (Рейтинг BETWEEN 1 AND 5),
    FOREIGN KEY (IDПользователя) REFERENCES Пользователи(IDПользователя)
);