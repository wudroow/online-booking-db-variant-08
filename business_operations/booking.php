<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';
require_once 'sportschool/AbstractRepository.php';
require_once 'sportschool/CoachRepository.php';
require_once 'sportschool/AthleteRepository.php';
require_once 'sportschool/EnrollmentRepository.php';
require_once 'sportschool/SportGroupRepository.php';

$pdo = Database::getInstance()->getConnection();
$serviceRepo = new SportGroupRepository($pdo);
$coachRepo = new CoachRepository($pdo);
$enrollmentRepo = new EnrollmentRepository($pdo);
$athleteRepo = new AthleteRepository($pdo);

$services = $serviceRepo->findAll();

$serviceId = $_GET['service_id'] ?? $_POST['service_id'] ?? null;
$coachId = $_GET['coach_id'] ?? $_POST['coach_id'] ?? null;
$date = $_GET['date'] ?? $_POST['date'] ?? null;
$slot = $_GET['slot'] ?? $_POST['slot'] ?? null;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $athleteName = trim($_POST['athlete_name'] ?? '');
    $athleteLastName = trim($_POST['athlete_last_name'] ?? '');
    $athleteBirth = trim($_POST['athlete_birth'] ?? '');
    $parentPhone = trim($_POST['parent_phone'] ?? '');
    $parentEmail = trim($_POST['parent_email'] ?? '');
    
    if (empty($athleteLastName)) $errors['athlete_last_name'] = 'Фамилия обязательна';
    if (empty($athleteName)) $errors['athlete_name'] = 'Имя обязательно';
    if (empty($athleteBirth)) $errors['athlete_birth'] = 'Дата рождения обязательна';
    if (empty($parentPhone)) $errors['parent_phone'] = 'Телефон обязателен';
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $checkSql = "SELECT COUNT(*) FROM enrollments e 
                         JOIN sport_groups sg ON e.group_id = sg.group_id 
                         WHERE sg.coach_id = :coach_id AND e.enrollment_date = CONCAT(:date, ' ', :slot)";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([':coach_id' => $coachId, ':date' => $date, ':slot' => $slot]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception('Это время только что заняли. Выберите другое.');
            }
            
            $athleteData = [
                'last_name' => $athleteLastName,
                'first_name' => $athleteName,
                'birth_date' => $athleteBirth,
                'parent_phone' => $parentPhone,
                'parent_email' => $parentEmail
            ];
            $athleteId = $athleteRepo->create($athleteData);
            
            $fullDatetime = $date . ' ' . $slot . ':00';
            $sql = "INSERT INTO enrollments (athlete_id, group_id, enrollment_date, status, enrollment_reason) 
                    VALUES (:athlete_id, :group_id, :enrollment_date, 'записана', 'постоянная_тренировка')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':athlete_id' => $athleteId,
                ':group_id' => $serviceId,
                ':enrollment_date' => $fullDatetime
            ]);
            
            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Запись создана! Номер: ' . $pdo->lastInsertId()];
            header("Location: appointments_list.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Запись на тренировку</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .booking-container{max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:8px;}
        .step{margin:20px 0;padding:20px;background:#f9f9f9;border-radius:8px;}
        .slots{display:flex;flex-wrap:wrap;gap:10px;margin-top:15px;}
        .slot-btn{padding:10px 20px;background:#007bff;color:white;border:none;border-radius:5px;cursor:pointer;}
        .slot-btn:hover{background:#0056b3;}
        .error{color:#dc3545;}
        .form-group{margin-bottom:15px;}
        .form-group label{display:block;margin-bottom:5px;font-weight:bold;}
        .form-group input,select{width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;}
        .btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;}
        .btn-success{background:#28a745;}
        .info{background:#e7f3ff;padding:10px;border-radius:4px;margin-bottom:20px;}
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <div class="booking-container">
        <h1>Запись на тренировку</h1>
        <?php if(isset($errors['general'])) echo '<div class="error">'.$errors['general'].'</div>'; ?>
        
        <?php if(!$slot): ?>
        <form method="GET" id="stepForm">
            <div class="step">
                <h3>Шаг 1: Выберите услугу и тренера</h3>
                <div class="form-group">
                    <label>Услуга (группа) *</label>
                    <select name="service_id" id="service_id" required>
                        <option value="">-- Выберите --</option>
                        <?php foreach($services as $s): ?>
                            <option value="<?=$s['group_id']?>" <?=($serviceId==$s['group_id'])?'selected':''?>><?=htmlspecialchars($s['group_name'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Тренер *</label>
                    <select name="coach_id" id="coach_id" required disabled>
                        <option value="">-- Сначала выберите услугу --</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Дата тренировки *</label>
                    <input type="date" name="date" id="date" value="<?=htmlspecialchars($date??'')?>" min="<?=date('Y-m-d')?>" required>
                </div>
                
                <div id="slotLoader" class="step" style="display:none;">
                    <h3>Шаг 2: Выберите время тренировки</h3>
                    <div id="slotsContainer" class="slots">Загрузка...</div>
                </div>
            </div>
        </form>
        <?php endif; ?>
        
        <?php if($slot): ?>
        <form method="POST" class="step">
            <input type="hidden" name="service_id" value="<?=$serviceId?>">
            <input type="hidden" name="coach_id" value="<?=$coachId?>">
            <input type="hidden" name="date" value="<?=$date?>">
            <input type="hidden" name="slot" value="<?=$slot?>">
            <input type="hidden" name="confirm" value="1">
            
            <h3>Шаг 3: Ваши данные</h3>
            
            <div class="form-group">
                <label>Фамилия спортсмена *</label>
                <input type="text" name="athlete_last_name" value="<?=htmlspecialchars($_POST['athlete_last_name']??'')?>">
                <?php if(isset($errors['athlete_last_name'])) echo '<span class="error">'.$errors['athlete_last_name'].'</span>'; ?>
            </div>
            
            <div class="form-group">
                <label>Имя спортсмена *</label>
                <input type="text" name="athlete_name" value="<?=htmlspecialchars($_POST['athlete_name']??'')?>">
                <?php if(isset($errors['athlete_name'])) echo '<span class="error">'.$errors['athlete_name'].'</span>'; ?>
            </div>
            
            <div class="form-group">
                <label>Дата рождения спортсмена *</label>
                <input type="date" name="athlete_birth" value="<?=htmlspecialchars($_POST['athlete_birth']??'')?>">
                <?php if(isset($errors['athlete_birth'])) echo '<span class="error">'.$errors['athlete_birth'].'</span>'; ?>
            </div>
            
            <div class="form-group">
                <label>Телефон родителя *</label>
                <input type="tel" name="parent_phone" value="<?=htmlspecialchars($_POST['parent_phone']??'')?>">
                <?php if(isset($errors['parent_phone'])) echo '<span class="error">'.$errors['parent_phone'].'</span>'; ?>
            </div>
            
            <div class="form-group">
                <label>Email родителя</label>
                <input type="email" name="parent_email" value="<?=htmlspecialchars($_POST['parent_email']??'')?>">
            </div>
            
            <div class="info">Вы выбрали: <?=date('d.m.Y',strtotime($date))?> в <?=$slot?></div>
            
            <button type="submit" class="btn btn-success">Подтвердить запись</button>
            <a href="booking.php" class="btn">Начать заново</a>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const coachSelect = document.getElementById('coach_id');
    const dateInput = document.getElementById('date');
    const slotLoader = document.getElementById('slotLoader');
    const slotsContainer = document.getElementById('slotsContainer');
    
    // Функция загрузки слотов
    function loadSlots(serviceId, coachIdVal, date) {
        if (!serviceId || !coachIdVal || !date) return;
        
        slotLoader.style.display = 'block';
        slotsContainer.innerHTML = 'Загрузка...';
        
        fetch(`get_available_slots.php?service_id=${serviceId}&coach_id=${coachIdVal}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.slots && data.slots.length > 0) {
                    let html = '';
                    data.slots.forEach(slot => {
                        html += `<button type="button" class="slot-btn" data-slot="${slot}">${slot}</button>`;
                    });
                    slotsContainer.innerHTML = html;
                    
                    document.querySelectorAll('.slot-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const form = document.getElementById('stepForm');
                            const hiddenSlot = document.createElement('input');
                            hiddenSlot.type = 'hidden';
                            hiddenSlot.name = 'slot';
                            hiddenSlot.value = this.dataset.slot;
                            form.appendChild(hiddenSlot);
                            form.submit();
                        });
                    });
                } else {
                    slotsContainer.innerHTML = '<p class="error">Нет свободных слотов на выбранную дату</p>';
                }
            })
            .catch(() => {
                slotsContainer.innerHTML = '<p class="error">Ошибка загрузки слотов</p>';
            });
    }
    
    // Загрузка тренеров при выборе услуги
    serviceSelect.addEventListener('change', function() {
        const serviceId = this.value;
        if (!serviceId) {
            coachSelect.innerHTML = '<option value="">-- Сначала выберите услугу --</option>';
            coachSelect.disabled = true;
            slotLoader.style.display = 'none';
            return;
        }
        
        coachSelect.innerHTML = '<option value="">Загрузка...</option>';
        coachSelect.disabled = true;
        
        fetch(`get_coaches_by_service.php?service_id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let options = '<option value="">-- Выберите тренера --</option>';
                    data.forEach(coach => {
                        options += `<option value="${coach.coach_id}">${coach.last_name} ${coach.first_name}</option>`;
                    });
                    coachSelect.innerHTML = options;
                    coachSelect.disabled = false;
                } else {
                    coachSelect.innerHTML = '<option value="">Нет тренеров для этой услуги</option>';
                    coachSelect.disabled = true;
                    slotLoader.style.display = 'none';
                }
            })
            .catch(() => {
                coachSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                coachSelect.disabled = true;
            });
    });
    
    // Выбор тренера и даты -> загружаем слоты
    function checkAndLoadSlots() {
        const serviceId = serviceSelect.value;
        const coachIdVal = coachSelect.value;
        const date = dateInput.value;
        
        if (serviceId && coachIdVal && coachIdVal !== '-- Выберите тренера --' && date) {
            loadSlots(serviceId, coachIdVal, date);
        } else {
            slotLoader.style.display = 'none';
        }
    }
    
    coachSelect.addEventListener('change', checkAndLoadSlots);
    dateInput.addEventListener('change', checkAndLoadSlots);
    
    // Если уже есть выбранные параметры (после перезагрузки)
    <?php if($serviceId && !$slot): ?>
        serviceSelect.value = <?=json_encode($serviceId)?>;
        serviceSelect.dispatchEvent(new Event('change'));
        setTimeout(() => {
            coachSelect.value = <?=json_encode($coachId)?>;
            checkAndLoadSlots();
        }, 500);
    <?php endif; ?>
});
</script>
</body>
</html>