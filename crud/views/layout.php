<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - Спортшкола</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($title) ?></h1>
        <nav class="menu">
    <a href="index.php?entity=athletes&action=list">Спортсмены</a>
    <a href="index.php?entity=coaches&action=list">Тренеры</a>
    <a href="index.php?entity=sport_groups&action=list">Услуги</a>
</nav>
        
       <?php
if ($entity === 'sport_groups') {
    if ($action === 'list') require 'views/list_groups.php';
    elseif ($action === 'view') require 'views/view_groups.php';
    elseif ($action === 'delete') require 'views/delete_groups.php';
    elseif ($action === 'create' || $action === 'edit') require 'views/form_groups.php';
} else {
    if ($action === 'list') require 'views/list.php';
    elseif ($action === 'view') require 'views/view.php';
    elseif ($action === 'delete') require 'views/delete.php';
    else require 'views/form.php';
}
?>
    </div>
</body>
</html>
