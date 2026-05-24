<?php
header('Content-Type: application/json');
require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';

$serviceId = $_GET['service_id'] ?? 0;
if (!$serviceId) {
    echo json_encode([]);
    exit;
}

$pdo = Database::getInstance()->getConnection();
$sql = "SELECT c.coach_id, c.last_name, c.first_name 
        FROM coaches c
        JOIN sport_groups sg ON c.coach_id = sg.coach_id
        WHERE sg.group_id = :service_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':service_id' => $serviceId]);
$coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($coaches);