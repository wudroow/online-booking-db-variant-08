<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';
$pdo = Database::getInstance()->getConnection();

$month = $_GET['month'] ?? date('Y-m');
$year = explode('-',$month)[0]; $monthNum = explode('-',$month)[1];

$daily = $pdo->prepare("SELECT DATE(e.enrollment_date) as date, COUNT(*) as count FROM enrollments e WHERE YEAR(e.enrollment_date)=:year AND MONTH(e.enrollment_date)=:month GROUP BY DATE(e.enrollment_date)");
$daily->execute([':year'=>$year,':month'=>$monthNum]); $dailyStats=$daily->fetchAll();

$coachRating = $pdo->query("SELECT c.last_name, c.first_name, COUNT(e.enrollment_id) as total FROM coaches c LEFT JOIN sport_groups sg ON c.coach_id=sg.coach_id LEFT JOIN enrollments e ON sg.group_id=e.group_id WHERE e.status!='отменена' OR e.status IS NULL GROUP BY c.coach_id ORDER BY total DESC")->fetchAll();

$cancelFrom = $_GET['cancel_from'] ?? date('Y-m-d',strtotime('-30 days'));
$cancelTo = $_GET['cancel_to'] ?? date('Y-m-d');
$cancelled = $pdo->prepare("SELECT DATE(e.enrollment_date) as date, COUNT(*) as cancelled FROM enrollments e WHERE e.status='отменена' AND DATE(e.enrollment_date) BETWEEN :from AND :to GROUP BY DATE(e.enrollment_date)");
$cancelled->execute([':from'=>$cancelFrom,':to'=>$cancelTo]); $cancelledStats=$cancelled->fetchAll();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Отчёты</title><link rel="stylesheet" href="css/style.css">
<style>.report-section{background:#f9f9f9;padding:20px;margin-bottom:30px;border-radius:8px;}</style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container"><h1>Отчёты</h1>

<div class="report-section"><h2>📊 Записи по дням</h2>
<form method="GET"><input type="month" name="month" value="<?=$month?>"><button type="submit">Показать</button></form>
<table class="table"><thead><tr><th>Дата</th><th>Количество</th></tr></thead>
<tbody><?php foreach($dailyStats as $r):?><tr><td><?=date('d.m.Y',strtotime($r['date']))?></td><td><?=$r['count']?></td></tr><?php endforeach;?>
<?php if(empty($dailyStats)):?><tr><td colspan="2">Нет данных</td></tr><?php endif;?>
</tbody></table>
<a href="export_csv.php?type=daily&month=<?=$month?>" class="btn">📎 CSV</a>
</div>

<div class="report-section"><h2>🏆 Рейтинг тренеров</h2>
<table class="table"><thead><tr><th>Тренер</th><th>Записей</th></tr></thead>
<tbody><?php foreach($coachRating as $r):?><tr><td><?=htmlspecialchars($r['last_name'].' '.$r['first_name'])?></td><td><?=$r['total']?></td></tr><?php endforeach;?></tbody></table>
<a href="export_csv.php?type=coaches" class="btn">📎 CSV</a>
</div>

<div class="report-section"><h2>❌ Отменённые записи</h2>
<form method="GET"><label>От: <input type="date" name="cancel_from" value="<?=$cancelFrom?>"></label><label>До: <input type="date" name="cancel_to" value="<?=$cancelTo?>"></label><button type="submit">Показать</button></form>
<table class="table"><thead><tr><th>Дата</th><th>Отмен</th></tr></thead>
<tbody><?php foreach($cancelledStats as $r):?><tr><td><?=date('d.m.Y',strtotime($r['date']))?></td><td><?=$r['cancelled']?></td></tr><?php endforeach;?></tbody></table>
<a href="export_csv.php?type=cancelled&from=<?=$cancelFrom?>&to=<?=$cancelTo?>" class="btn">📎 CSV</a>
</div>
</div>
</body>
</html>