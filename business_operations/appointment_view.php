<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';

$pdo = Database::getInstance()->getConnection();
$id = $_GET['id'] ?? 0;
if(!$id) header("Location: appointments_list.php");

$sql="SELECT e.*, a.last_name as athlete_last_name, a.first_name as athlete_first_name, a.parent_phone, a.parent_email, a.birth_date,
      sg.group_name as service_name, c.last_name as coach_last_name, c.first_name as coach_first_name
      FROM enrollments e
      JOIN athletes a ON e.athlete_id=a.athlete_id
      JOIN sport_groups sg ON e.group_id=sg.group_id
      JOIN coaches c ON sg.coach_id=c.coach_id
      WHERE e.enrollment_id=:id";
$stmt=$pdo->prepare($sql); $stmt->execute([':id'=>$id]); $a=$stmt->fetch();
if(!$a) header("Location: appointments_list.php");

// Определяем статус: NULL или пусто = отменена
$rawStatus = trim($a['status'] ?? '');
if($rawStatus === '') {
    $status = 'отменена';
} else {
    $status = $rawStatus;
}

// Обработка отмены (проставляем отменена)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])){
    $update = $pdo->prepare("UPDATE enrollments SET status = 'отменена' WHERE enrollment_id = :id");
    $update->execute([':id' => $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Запись отменена'];
    header("Location: appointment_view.php?id=" . $id);
    exit;
}

$showButtons = ($status != 'отменена');
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Детали записи</title><link rel="stylesheet" href="css/style.css">
<style>.card{background:#f9f9f9;padding:20px;border-radius:8px;margin-bottom:20px;}.btn-group{display:flex;gap:10px;}.btn-danger{background:#dc3545;}</style></head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
<h1>Детали записи</h1>
<div class="card">
<p><strong>Номер записи:</strong> <?=$a['enrollment_id']?></p>
<p><strong>Дата и время:</strong> <?=date('d.m.Y H:i',strtotime($a['enrollment_date']))?></p>
<p><strong>Спортсмен:</strong> <?=htmlspecialchars($a['athlete_last_name'].' '.$a['athlete_first_name'])?></p>
<p><strong>Дата рождения:</strong> <?=date('d.m.Y',strtotime($a['birth_date']))?></p>
<p><strong>Телефон родителя:</strong> <?=htmlspecialchars($a['parent_phone'])?></p>
<p><strong>Email:</strong> <?=htmlspecialchars($a['parent_email']??'-')?></p>
<p><strong>Услуга:</strong> <?=htmlspecialchars($a['service_name'])?></p>
<p><strong>Тренер:</strong> <?=htmlspecialchars($a['coach_last_name'].' '.$a['coach_first_name'])?></p>
<p><strong>Статус:</strong> 
    <?php if($status == 'отменена'): ?>
        <span style="color:#dc3545; font-weight:bold;">❌ ОТМЕНЕНА</span>
    <?php else: ?>
        <span style="color:#28a745; font-weight:bold;">✅ АКТИВНА</span>
    <?php endif; ?>
</p>
</div>
<div class="btn-group">
<a href="appointments_list.php" class="btn">← Назад</a>
<?php if($showButtons): ?>
    <a href="reschedule.php?id=<?=$id?>" class="btn btn-primary">📅 Перенести</a>
    <form method="POST" onsubmit="return confirm('Отменить запись?');" style="display:inline;">
        <button type="submit" name="cancel" class="btn btn-danger">❌ Отменить</button>
    </form>
<?php endif; ?>
</div>
</div>
</body>
</html>