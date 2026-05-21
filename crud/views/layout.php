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
        
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                <?php unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
        
        <nav class="menu">
            <a href="index.php?entity=athletes&action=list">Спортсмены</a>
            <a href="index.php?entity=coaches&action=list">Тренеры</a>
            <a href="index.php?entity=sport_groups&action=list">Группы</a>
        </nav>
        
        <?php
        if ($action === 'list') require 'views/list.php';
        elseif ($action === 'view') require 'views/view.php';
        elseif ($action === 'delete') require 'views/delete.php';
        else require 'views/form.php';
        ?>
    </div>
</body>
</html>