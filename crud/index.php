<?php
session_start();
require_once 'Database.php';
require_once 'RepositoryException.php';
require_once 'AbstractRepository.php';
require_once 'CoachRepository.php';
require_once 'AthleteRepository.php';
require_once 'EnrollmentRepository.php';
require_once 'controllers/CrudController.php';

$entity = $_GET['entity'] ?? 'athletes';
$action = $_GET['action'] ?? 'list';

$controller = new CrudController();
$controller->handle($entity, $action);