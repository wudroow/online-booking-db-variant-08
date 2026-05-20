-- =====================================================
-- Вариант 8: Спортшкола (отбор и постоянные тренировки)
-- Студент: Выдрина Виктория
-- Группа: 09.02.09
-- Дата: 20.05.2026
-- Хостинг: Beget (без DROP DATABASE)
-- =====================================================

USE a92371k6_wu;

-- Таблица тренеров
CREATE TABLE IF NOT EXISTS coaches (
    coach_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    patronymic VARCHAR(50),
    phone VARCHAR(20) NOT NULL UNIQUE,
    specialization VARCHAR(100),
    hire_date DATE NOT NULL,
    max_group_capacity INT NOT NULL CHECK (max_group_capacity BETWEEN 5 AND 30)
);

-- Таблица возрастных групп
CREATE TABLE IF NOT EXISTS age_groups (
    age_group_id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(50) NOT NULL UNIQUE,
    min_age INT NOT NULL CHECK (min_age >= 5),
    max_age INT NOT NULL CHECK (max_age <= 18),
    CHECK (min_age < max_age)
);

-- Таблица спортсменов
CREATE TABLE IF NOT EXISTS athletes (
    athlete_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    patronymic VARCHAR(50),
    birth_date DATE NOT NULL,
    phone VARCHAR(20),
    parent_phone VARCHAR(20) NOT NULL,
    parent_email VARCHAR(100),
    medical_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица групп (связь тренера и возрастной группы)
CREATE TABLE IF NOT EXISTS sport_groups (
    group_id INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT NOT NULL,
    age_group_id INT NOT NULL,
    group_name VARCHAR(50) NOT NULL,
    current_capacity INT DEFAULT 0,
    schedule_description VARCHAR(255),
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (age_group_id) REFERENCES age_groups(age_group_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY unique_coach_agegroup (coach_id, age_group_id)
);

-- Таблица записей на тренировки
CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    group_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('записана', 'отчислена', 'переведена', 'пробное_занятие') DEFAULT 'записана',
    enrollment_reason ENUM('отбор', 'постоянная_тренировка') NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (athlete_id) REFERENCES athletes(athlete_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (group_id) REFERENCES sport_groups(group_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY unique_active_enrollment (athlete_id, group_id, status)
);

-- Таблица достижений
CREATE TABLE IF NOT EXISTS achievements (
    achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    achievement_type ENUM('грамота', 'диплом', 'разряд', 'кубок') NOT NULL,
    title VARCHAR(200) NOT NULL,
    issue_date DATE NOT NULL,
    rank_value VARCHAR(20),
    FOREIGN KEY (athlete_id) REFERENCES athletes(athlete_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Таблица тренировок (расписание)
CREATE TABLE IF NOT EXISTS trainings (
    training_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    training_datetime DATETIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    topic VARCHAR(200),
    attended_count INT DEFAULT 0,
    FOREIGN KEY (group_id) REFERENCES sport_groups(group_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_training_slot (group_id, training_datetime)
);

-- Таблица посещаемости
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    training_id INT NOT NULL,
    attended BOOLEAN DEFAULT FALSE,
    reason_for_absence VARCHAR(255),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (training_id) REFERENCES trainings(training_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_attendance (enrollment_id, training_id)
);

-- =====================================================
-- ТЕСТОВЫЕ ДАННЫЕ
-- =====================================================

INSERT IGNORE INTO coaches (last_name, first_name, patronymic, phone, specialization, hire_date, max_group_capacity) VALUES
('Смирнов', 'Алексей', 'Игоревич', '+79161234567', 'Плавание', '2015-08-15', 15),
('Кузнецова', 'Елена', 'Павловна', '+79162345678', 'Гимнастика', '2018-01-20', 12),
('Морозов', 'Дмитрий', 'Андреевич', '+79163456789', 'Футбол', '2010-03-10', 20),
('Волкова', 'Анна', 'Сергеевна', '+79164567890', 'Легкая атлетика', '2020-09-01', 10);

INSERT IGNORE INTO age_groups (group_name, min_age, max_age) VALUES
('Младшая (5-7 лет)', 5, 7),
('Средняя (8-11 лет)', 8, 11),
('Старшая (12-14 лет)', 12, 14),
('Юниоры (15-18 лет)', 15, 18);

INSERT IGNORE INTO sport_groups (coach_id, age_group_id, group_name, current_capacity, schedule_description) VALUES
(1, 1, 'Плавательная младшая', 12, 'Вт/Чт 16:00-17:00'),
(1, 2, 'Плавательная средняя', 14, 'Пн/Ср/Пт 17:00-18:30'),
(2, 1, 'Гимнастика малыши', 10, 'Вт/Чт/Сб 15:00-16:00'),
(2, 3, 'Гимнастика старшие', 8, 'Пн/Ср 18:00-19:30'),
(3, 3, 'Футбол юноши', 18, 'Вт/Чт/Сб 16:00-17:30');

INSERT IGNORE INTO athletes (last_name, first_name, patronymic, birth_date, phone, parent_phone, parent_email, medical_note) VALUES
('Иванов', 'Иван', 'Алексеевич', '2018-05-12', NULL, '+79171234567', 'ivanov_parent@mail.ru', 'Аллергии нет'),
('Петрова', 'Мария', 'Дмитриевна', '2016-09-23', '+79172345678', '+79172345678', 'petrova@mail.ru', 'Бронхиальная астма'),
('Сидоров', 'Артём', 'Игоревич', '2012-03-07', NULL, '+79173456789', 'sidorov_family@mail.ru', NULL),
('Козлова', 'София', 'Андреевна', '2010-11-14', '+79174567890', '+79174567890', 'kozlovas@mail.ru', 'Сколиоз 1 степени'),
('Николаев', 'Егор', 'Павлович', '2014-07-19', NULL, '+79175678901', 'nikolaevs@mail.ru', NULL),
('Михайлова', 'Алиса', 'Романовна', '2019-01-30', '+79176789012', '+79176789012', 'alisa.m@mail.ru', 'Аллергия на молоко');

INSERT IGNORE INTO enrollments (athlete_id, group_id, enrollment_date, status, enrollment_reason) VALUES
(1, 1, '2025-09-01', 'записана', 'постоянная_тренировка'),
(2, 3, '2025-09-01', 'записана', 'отбор'),
(3, 4, '2025-09-10', 'записана', 'постоянная_тренировка'),
(4, 5, '2025-08-20', 'записана', 'постоянная_тренировка'),
(5, 2, '2026-01-15', 'записана', 'отбор'),
(6, 1, '2026-02-01', 'записана', 'пробное_занятие');

INSERT IGNORE INTO achievements (athlete_id, achievement_type, title, issue_date, rank_value) VALUES
(1, 'грамота', 'Победитель школьных соревнований', '2025-12-10', NULL),
(2, 'разряд', '3 юношеский разряд по гимнастике', '2025-11-15', '3 юношеский'),
(3, 'диплом', 'Призёр городской олимпиады', '2026-01-20', NULL),
(4, 'кубок', 'Лучший бомбардир турнира', '2025-10-05', NULL),
(3, 'грамота', 'За успехи в спорте', '2026-02-14', NULL);

INSERT IGNORE INTO trainings (group_id, training_datetime, duration_minutes, topic, attended_count) VALUES
(1, '2026-05-25 16:00:00', 60, 'Техника плавания', 0),
(1, '2026-05-27 16:00:00', 60, 'Дыхание', 0),
(2, '2026-05-26 17:00:00', 90, 'Кроль на груди', 0),
(3, '2026-05-26 15:00:00', 60, 'Растяжка', 0),
(4, '2026-05-27 18:00:00', 90, 'Упражнения на брусьях', 0),
(5, '2026-05-28 16:00:00', 90, 'Тактика защиты', 0);

-- =====================================================
-- ЗАПРОСЫ
-- =====================================================

-- Запрос 1. JOIN трёх таблиц
SELECT 
    a.last_name AS athlete_last_name,
    a.first_name AS athlete_first_name,
    ag.group_name AS age_group,
    c.last_name AS coach_last_name,
    e.enrollment_date,
    e.enrollment_reason,
    e.status
FROM enrollments e
JOIN athletes a ON e.athlete_id = a.athlete_id
JOIN sport_groups g ON e.group_id = g.group_id
JOIN coaches c ON g.coach_id = c.coach_id
JOIN age_groups ag ON g.age_group_id = ag.age_group_id
ORDER BY e.enrollment_date DESC;

-- Запрос 2. Группировка с HAVING (тренеры с заполнением >80%)
SELECT 
    c.last_name,
    c.first_name,
    AVG(g.current_capacity / c.max_group_capacity * 100) AS avg_fill_percentage
FROM coaches c
JOIN sport_groups g ON c.coach_id = g.coach_id
GROUP BY c.coach_id
HAVING avg_fill_percentage > 80
ORDER BY avg_fill_percentage DESC;

-- Запрос 3. Оконная функция (рейтинг спортсменов по достижениям)
SELECT 
    athlete_id,
    last_name,
    first_name,
    achievement_count,
    RANK() OVER (ORDER BY achievement_count DESC) AS rating_rank
FROM (
    SELECT 
        a.athlete_id,
        a.last_name,
        a.first_name,
        COUNT(ach.achievement_id) AS achievement_count
    FROM athletes a
    LEFT JOIN achievements ach ON a.athlete_id = ach.athlete_id
    GROUP BY a.athlete_id
) AS athlete_stats
ORDER BY rating_rank;