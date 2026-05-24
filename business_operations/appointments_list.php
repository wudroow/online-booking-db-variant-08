<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';

$pdo = Database::getInstance()->getConnection();

$page = $_GET['page'] ?? 1;
$status = $_GET['status'] ?? '';
$coachId = $_GET['coach_id'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$limit = 20;
$offset = ($page-1)*$limit;

$where = '1=1'; $params = [];
if($status && $status!='all'){ $where.=' AND e.status=:status'; $params[':status']=$status; }
if($coachId){ $where.=' AND c.coach_id=:coach_id'; $params[':coach_id']=$coachId; }
if($dateFrom){ $where.=' AND DATE(e.enrollment_date)>=:date_from'; $params[':date_from']=$dateFrom; }
if($dateTo){ $where.=' AND DATE(e.enrollment_date)<=:date_to'; $params[':date_to']=$dateTo; }

$sql="SELECT e.*, a.last_name as athlete_last_name, a.first_name as athlete_first_name,
      sg.group_name as service_name, c.last_name as coach_last_name, c.first_name as coach_first_name
      FROM enrollments e
      JOIN athletes a ON e.athlete_id=a.athlete_id
      JOIN sport_groups sg ON e.group_id=sg.group_id
      JOIN coaches c ON sg.coach_id=c.coach_id
      WHERE $where ORDER BY e.enrollment_date DESC LIMIT $limit OFFSET $offset";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $appointments=$stmt->fetchAll();

$countSql="SELECT COUNT(*) FROM enrollments e JOIN sport_groups sg ON e.group_id=sg.group_id JOIN coaches c ON sg.coach_id=c.coach_id WHERE $where";
$countStmt=$pdo->prepare($countSql); $countStmt->execute($params); $total=$countStmt->fetchColumn(); $totalPages=ceil($total/$limit);

$coaches=$pdo->query("SELECT coach_id,last_name,first_name FROM coaches")->fetchAll();
$statuses=['записана','подтверждена','отменена','завершена'];
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Список записей</title><link rel="stylesheet" href="css/style.css">
<style>
.filters{background:#f9f9f9;padding:15px;margin-bottom:20px;border-radius:8px;display:flex;flex-wrap:wrap;gap:15px;align-items:flex-end;}
.filter-group{display:flex;flex-direction:column;}
.filter-group input,.filter-group select{padding:6px 10px;width:150px;}
.status-badge{padding:4px 8px;border-radius:4px;font-size:12px;color:white;display:inline-block;}
.status-записана{background:#ffc107;color:#333;}
.status-подтверждена{background:#28a745;}
.status-отменена{background:#dc3545;}
.status-завершена{background:#6c757d;}
.pagination{margin-top:20px;display:flex;gap:5px;justify-content:center;}
.pagination a,.pagination span{padding:5px 10px;border:1px solid #ddd;text-decoration:none;}
.pagination .active{background:#007bff;color:white;}
.btn-sm{padding:6px 12px;font-size:12px;border-radius:4px;display:inline-block;background:#007bff;color:white;text-decoration:none;}
</style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
<h1>Управление записями</h1>
<?php if(isset($_SESSION['flash'])):?>
<div class="alert alert-<?=$_SESSION['flash']['type']?>"><?=htmlspecialchars($_SESSION['flash']['message'])?></div>
<?php unset($_SESSION['flash']); endif;?>

<form method="GET" class="filters">
<div class="filter-group"><label>Статус</label><select name="status"><option value="">Все</option>
<?php foreach($statuses as $s):?><option value="<?=$s?>" <?=($status==$s)?'selected':''?>><?=$s?></option><?php endforeach;?>
</select></div>
<div class="filter-group"><label>Тренер</label><select name="coach_id"><option value="">Все</option>
<?php foreach($coaches as $c):?><option value="<?=$c['coach_id']?>" <?=($coachId==$c['coach_id'])?'selected':''?>><?=htmlspecialchars($c['last_name'].' '.$c['first_name'])?></option><?php endforeach;?>
</select></div>
<div class="filter-group"><label>Дата от</label><input type="date" name="date_from" value="<?=htmlspecialchars($dateFrom)?>"></div>
<div class="filter-group"><label>Дата до</label><input type="date" name="date_to" value="<?=htmlspecialchars($dateTo)?>"></div>
<div class="filter-group"><button type="submit" class="btn-sm" style="background:#007bff;">Фильтр</button></div>
<div class="filter-group"><a href="appointments_list.php" class="btn-sm" style="background:#6c757d;">Сбросить</a></div>
<div class="filter-group"><a href="booking.php" class="btn-sm" style="background:#28a745;">+ Новая запись</a></div>
</form>

<table class="table"><thead><tr><th>Дата/время</th><th>Спортсмен</th><th>Услуга</th><th>Тренер</th><th>Статус</th><th>Действия</th></tr></thead>
<tbody><?php if(empty($appointments)):?><tr><td colspan="6">Нет записей</td</tr><?php
else: foreach($appointments as $a):
    $displayStatus = trim($a['status'] ?? '');
    if($displayStatus === '') {
        $displayStatus = 'отменена';
    }
?>
<tr>
    <td><?=date('d.m.Y H:i',strtotime($a['enrollment_date']))?></td>
    <td><?=htmlspecialchars($a['athlete_last_name'].' '.$a['athlete_first_name'])?></td>
    <td><?=htmlspecialchars($a['service_name'])?></td>
    <td><?=htmlspecialchars($a['coach_last_name'].' '.$a['coach_first_name'])?></td>
    <td><span class="status-badge status-<?=$displayStatus?>"><?=$displayStatus?></span></td>
    <td><a href="appointment_view.php?id=<?=$a['enrollment_id']?>" class="btn-sm">👁️ Просмотр</a></td>
</tr>
<?php endforeach; endif;?></tbody>
</table>

<div class="pagination"><?php for($i=1;$i<=$totalPages;$i++):?>
<a href="?page=<?=$i?>&status=<?=urlencode($status)?>&coach_id=<?=$coachId?>&date_from=<?=$dateFrom?>&date_to=<?=$dateTo?>" class="<?=($i==$page)?'active':''?>"><?=$i?></a>
<?php endfor;?></div>
</div>
</body>
</html>