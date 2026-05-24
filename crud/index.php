<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/sportschool/config.php';
require_once __DIR__ . '/sportschool/Database.php';
require_once __DIR__ . '/sportschool/AbstractRepository.php';
require_once __DIR__ . '/sportschool/CoachRepository.php';
require_once __DIR__ . '/sportschool/AthleteRepository.php';
require_once __DIR__ . '/sportschool/EnrollmentRepository.php';
require_once __DIR__ . '/sportschool/SportGroupRepository.php';
require_once __DIR__ . '/controllers/CrudController.php';

$entity = $_GET['entity'] ?? 'athletes';
$action = $_GET['action'] ?? 'list';

$controller = new CrudController();
$controller->handle($entity, $action);
