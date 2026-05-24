<?php
header('Content-Type: application/json');
require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';
require_once 'sportschool/AbstractRepository.php';
require_once 'sportschool/EnrollmentRepository.php';
require_once 'sportschool/SportGroupRepository.php';

$serviceId = $_GET['service_id'] ?? null;
$coachId = $_GET['coach_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$serviceId || !$coachId || !$date) {
    echo json_encode(['slots' => []]);
    exit;
}

$pdo = Database::getInstance()->getConnection();

// Настройки рабочего дня
$startHour = 9;
$endHour = 18;
$breakStart = 13;
$breakEnd = 14;
$duration = 60; // длительность тренировки в минутах

// Получаем уже занятые слоты
$bookedSql = "SELECT TIME(enrollment_date) as slot_time 
              FROM enrollments e
              JOIN sport_groups sg ON e.group_id = sg.group_id
              JOIN coaches c ON sg.coach_id = c.coach_id
              WHERE c.coach_id = :coach_id AND DATE(e.enrollment_date) = :date";
$bookedStmt = $pdo->prepare($bookedSql);
$bookedStmt->execute([':coach_id' => $coachId, ':date' => $date]);
$bookedSlots = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

$slots = [];
for ($hour = $startHour; $hour < $endHour; $hour++) {
    if ($hour >= $breakStart && $hour < $breakEnd) continue;
    $time = sprintf('%02d:00', $hour);
    if (!in_array($time, $bookedSlots)) {
        $slots[] = $time;
    }
}

echo json_encode(['slots' => $slots]);