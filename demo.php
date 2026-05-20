<?php
/**
 * Демонстрационный скрипт для проверки работы репозиториев
 */

require_once 'config.php';
require_once 'Database.php';
require_once 'RepositoryException.php';
require_once 'AbstractRepository.php';
require_once 'CoachRepository.php';
require_once 'AthleteRepository.php';
require_once 'EnrollmentRepository.php';

echo '<!DOCTYPE html>
<html>
<head>
    <title>Демонстрация работы DAL - Спортшкола</title>
    <style>
        body { font-family: monospace; background: #f5f5f5; padding: 20px; }
        .section { background: white; margin: 20px 0; padding: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        pre { background: #eee; padding: 10px; overflow-x: auto; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Уровень доступа к данным (DAL) для спортшколы</h1>
    <p>Вариант 8 | Выдрина Виктория | Группа 09.02.09</p>';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo '<div class="section"><h2>✓ Подключение к базе данных успешно</h2></div>';

    $coachRepo = new CoachRepository($pdo);
    $athleteRepo = new AthleteRepository($pdo);
    $enrollmentRepo = new EnrollmentRepository($pdo);

    echo '<div class="section">';
    echo '<h2>1. findAll() - Список всех тренеров</h2>';
    $coaches = $coachRepo->findAll();
    echo '<pre>';
    print_r($coaches);
    echo '</pre></div>';

    echo '<div class="section">';
    echo '<h2>2. findById() - Тренер с ID = 1</h2>';
    try {
        $coach = $coachRepo->findById(1);
        echo '<pre>';
        print_r($coach);
        echo '</pre>';
    } catch (RepositoryException $e) {
        echo '<p class="error">Ошибка: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    echo '<div class="section">';
    echo '<h2>3. getCoachesWithHighFillRate() - Тренеры с заполнением групп более 80%</h2>';
    $topCoaches = $coachRepo->getCoachesWithHighFillRate(80);
    echo '<pre>';
    print_r($topCoaches);
    echo '</pre></div>';

    echo '<div class="section">';
    echo '<h2>4. getRatingByAchievements() - Рейтинг спортсменов по достижениям</h2>';
    $rating = $athleteRepo->getRatingByAchievements();
    echo '<pre>';
    print_r($rating);
    echo '</pre></div>';

    echo '<div class="section">';
    echo '<h2>5. getEnrollmentsWithDetails() - Записи с подробной информацией (JOIN)</h2>';
    $enrollments = $enrollmentRepo->getEnrollmentsWithDetails();
    echo '<pre>';
    print_r($enrollments);
    echo '</pre></div>';

    echo '<div class="section">';
    echo '<h2>6. create() - Создание новой записи (с транзакцией)</h2>';
    try {
        $newEnrollmentId = $enrollmentRepo->create([
            'athlete_id' => 2,
            'group_id' => 2,
            'enrollment_date' => '2026-05-20',
            'enrollment_reason' => 'отбор',
            'status' => 'записана'
        ]);
        echo '<p class="success">✓ Запись успешно создана. ID новой записи: ' . $newEnrollmentId . '</p>';
        $updatedEnrollments = $enrollmentRepo->getEnrollmentsWithDetails();
        echo '<pre>';
        print_r($updatedEnrollments);
        echo '</pre>';
    } catch (RepositoryException $e) {
        echo '<p class="error">Ошибка при создании записи: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    echo '<div class="section">';
    echo '<h2>7. cancel() - Отмена записи (с транзакцией)</h2>';
    try {
        $allEnrollments = $enrollmentRepo->findAll();
        if (!empty($allEnrollments)) {
            $enrollmentToCancel = $allEnrollments[0]['enrollment_id'];
            $enrollmentRepo->cancel($enrollmentToCancel);
            echo '<p class="success">✓ Запись ID ' . $enrollmentToCancel . ' успешно отменена</p>';
        } else {
            echo '<p>Нет записей для отмены</p>';
        }
    } catch (RepositoryException $e) {
        echo '<p class="error">Ошибка при отмене: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

    echo '<div class="section">';
    echo '<h2>8. delete() - Удаление спортсмена</h2>';
    try {
        $athleteRepo->delete(6);
        echo '<p class="success">✓ Спортсмен с ID 6 успешно удалён</p>';
        $remainingAthletes = $athleteRepo->findAll();
        echo '<pre>';
        print_r($remainingAthletes);
        echo '</pre>';
    } catch (RepositoryException $e) {
        echo '<p class="error">Ошибка при удалении: ' . $e->getMessage() . '</p>';
    } catch (PDOException $e) {
        echo '<p class="error">Ошибка БД при удалении: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';

} catch (PDOException $e) {
    echo '<div class="section error">';
    echo '<h2>Ошибка подключения к базе данных</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '</div>';
}

echo '</body></html>';