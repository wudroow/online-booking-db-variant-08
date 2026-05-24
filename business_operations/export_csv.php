<?php
require_once 'sportschool/config.php';
require_once 'sportschool/Database.php';
$pdo = Database::getInstance()->getConnection();
$type = $_GET['type'] ?? '';
header('Content-Type: text/csv; charset=utf-8');

if($type==='daily'){
    $month=$_GET['month']??date('Y-m');
    $y=explode('-',$month)[0]; $m=explode('-',$month)[1];
    $data=$pdo->prepare("SELECT DATE(enrollment_date) as date, COUNT(*) as count FROM enrollments WHERE YEAR(enrollment_date)=:year AND MONTH(enrollment_date)=:month GROUP BY DATE(enrollment_date)");
    $data->execute([':year'=>$y,':month'=>$m]); $rows=$data->fetchAll();
    header('Content-Disposition: attachment; filename="daily_stats_'.$month.'.csv"');
    $out=fopen('php://output','w'); fputcsv($out,['Дата','Количество']);
    foreach($rows as $r) fputcsv($out,[date('d.m.Y',strtotime($r['date'])),$r['count']]);
    fclose($out);
}
elseif($type==='coaches'){
    $data=$pdo->query("SELECT c.last_name, c.first_name, COUNT(e.enrollment_id) as total FROM coaches c LEFT JOIN sport_groups sg ON c.coach_id=sg.coach_id LEFT JOIN enrollments e ON sg.group_id=e.group_id WHERE e.status!='отменена' OR e.status IS NULL GROUP BY c.coach_id ORDER BY total DESC");
    $rows=$data->fetchAll();
    header('Content-Disposition: attachment; filename="coaches_rating.csv"');
    $out=fopen('php://output','w'); fputcsv($out,['Тренер','Количество записей']);
    foreach($rows as $r) fputcsv($out,[$r['last_name'].' '.$r['first_name'],$r['total']]);
    fclose($out);
}
elseif($type==='cancelled'){
    $from=$_GET['from']??date('Y-m-d',strtotime('-30 days'));
    $to=$_GET['to']??date('Y-m-d');
    $data=$pdo->prepare("SELECT DATE(enrollment_date) as date, COUNT(*) as cancelled FROM enrollments WHERE status='отменена' AND DATE(enrollment_date) BETWEEN :from AND :to GROUP BY DATE(enrollment_date)");
    $data->execute([':from'=>$from,':to'=>$to]); $rows=$data->fetchAll();
    header('Content-Disposition: attachment; filename="cancelled_stats.csv"');
    $out=fopen('php://output','w'); fputcsv($out,['Дата','Количество отмен']);
    foreach($rows as $r) fputcsv($out,[date('d.m.Y',strtotime($r['date'])),$r['cancelled']]);
    fclose($out);
}