<?php
// Включаем отображение всех ошибок PHP на экране
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Главный входной файл (Front Controller)
 */

// 1. Подключаем конфигурацию из папки sportschool
if (file_exists(__DIR__ . '/sportschool/config.php')) {
    require_once __DIR__ . '/sportschool/config.php';
} else {
    die('Ошибка: Файл config.php не найден в папке sportschool!');
}

// 2. Подключаем системные файлы и базу данных
require_once __DIR__ . '/sportschool/Database.php';

// Подключаем РЕПОЗИТОРИИ (Сначала родительский абстрактный класс, потом класс спортсменов!)
// Предполагаем, что они лежат в папке sportschool
require_once __DIR__ . '/sportschool/AbstractRepository.php'; 
require_once __DIR__ . '/sportschool/AthleteRepository.php'; 

// Подключаем контроллер
require_once __DIR__ . '/controllers/CrudController.php';

// 3. Запускаем обработку запроса
try {
    $controller = new CrudController();
    $controller->handle('athletes', 'list');

} catch (Throwable $e) {
    echo "<h3>Произошла ошибка в коде:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<p>В файле: <b>" . $e->getFile() . "</b> на строке <b>" . $e->getLine() . "</b></p>";
}
