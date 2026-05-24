<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';

$pdo = Database::getInstance()->getConnection();
$id = $_GET['id'] ?? 0;
if(!$id) header("Location: appointments_list.php");

$sql="SELECT e.*, sg.group_id as service_id, sg.coach_id FROM enrollments e JOIN sport_groups sg ON e.group_id=sg.group_id WHERE e.enrollment_id=:id";
$stmt=$pdo->prepare($sql); $stmt->execute([':id'=>$id]); $app=$stmt->fetch();
if(!$app) header("Location: appointments_list.php");

$serviceId=$app['service_id']; $coachId=$app['coach_id']; $error=null;
$newDate=$_POST['new_date']??null; $newSlot=$_POST['new_slot']??null;

if($_SERVER['REQUEST_METHOD']==='POST' && $newDate && $newSlot){
    $checkSql="SELECT COUNT(*) FROM enrollments e JOIN sport_groups sg ON e.group_id=sg.group_id WHERE sg.coach_id=:coach_id AND e.enrollment_date=CONCAT(:date,' ',:slot)";
    $checkStmt=$pdo->prepare($checkSql);
    $checkStmt->execute([':coach_id'=>$coachId,':date'=>$newDate,':slot'=>$newSlot]);
    if($checkStmt->fetchColumn()>0) $error='Это время уже занято';
    else{
        $newDatetime=$newDate.' '.$newSlot.':00';
        $upd=$pdo->prepare("UPDATE enrollments SET enrollment_date=:new WHERE enrollment_id=:id");
        $upd->execute([':new'=>$newDatetime,':id'=>$id]);
        $_SESSION['flash']=['type'=>'success','message'=>'Запись перенесена на '.date('d.m.Y H:i',strtotime($newDatetime))];
        header("Location: appointment_view.php?id=$id"); exit;
    }
}

$startHour=9;$endHour=18;$breakStart=13;$breakEnd=14;
$booked=$pdo->prepare("SELECT TIME(enrollment_date) FROM enrollments e JOIN sport_groups sg ON e.group_id=sg.group_id WHERE sg.coach_id=:coach_id AND DATE(e.enrollment_date)=:date");
$booked->execute([':coach_id'=>$coachId,':date'=>date('Y-m-d')]); $bookedSlots=$booked->fetchAll(PDO::FETCH_COLUMN);
$slots=[]; for($h=$startHour;$h<$endHour;$h++){ if($h>=$breakStart && $h<$breakEnd) continue; $t=sprintf('%02d:00',$h); if(!in_array($t,$bookedSlots)) $slots[]=$t; }
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Перенос записи</title><link rel="stylesheet" href="css/style.css">
<style>.slots{display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;}.slot-btn{padding:8px 16px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;}</style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container"><h1>Перенос записи</h1>
<?php if($error) echo '<div class="error">'.$error.'</div>'; ?>
<form method="POST">
<div class="form-group"><label>Новая дата</label><input type="date" name="new_date" id="new_date" min="<?=date('Y-m-d')?>" required></div>
<div class="form-group"><label>Новое время</label><div id="slotsContainer" class="slots">Выберите дату</div></div>
<input type="hidden" name="new_slot" id="new_slot">
<button type="submit" class="btn btn-primary" id="submitBtn" disabled>Подтвердить перенос</button>
<a href="appointment_view.php?id=<?=$id?>" class="btn">Отмена</a>
</form>
</div>
<script>
document.getElementById('new_date').addEventListener('change',function(){
    const date=this.value; if(!date) return;
    fetch(`get_available_slots.php?service_id=<?=$serviceId?>&coach_id=<?=$coachId?>&date=${date}`)
    .then(r=>r.json()).then(data=>{
        const cont=document.getElementById('slotsContainer');
        if(data.slots && data.slots.length){
            let html=''; data.slots.forEach(s=>{html+=`<button type="button" class="slot-btn" data-slot="${s}">${s}</button>`;});
            cont.innerHTML=html;
            document.querySelectorAll('.slot-btn').forEach(btn=>{
                btn.addEventListener('click',function(){
                    document.querySelectorAll('.slot-btn').forEach(b=>b.style.background='#007bff');
                    this.style.background='#28a745';
                    document.getElementById('new_slot').value=this.dataset.slot;
                    document.getElementById('submitBtn').disabled=false;
                });
            });
        }else cont.innerHTML='<p>Нет свободных слотов</p>';
    });
});
</script>
</body>
</html>