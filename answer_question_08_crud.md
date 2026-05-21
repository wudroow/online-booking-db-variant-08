# Контрольный вопрос №8

**Студент:** Выдрина Виктория  
**Группа:** 09.02.09  
**Вариант:** 8  
**Работа:** Проектирование и реализация пользовательского интерфейса. Часть 1 (Справочники)

## Формулировка вопроса

Как в вашем коде реализована фильтрация и сортировка данных? Покажите, как метод findAll() принимает параметры $where, $orderBy, $limit.

## Ответ

В моём уровне доступа к данным метод findAll класса AbstractRepository принимает три необязательных параметра: $where (условие WHERE), $orderBy (сортировка) и $limit (ограничение количества записей).

Пример из метода listAction контроллера CrudController:

$where = '';
$params = [];
if ($search) {
    if ($entity === 'sport_groups') {
        $where = "group_name LIKE :search";
    } else {
        $where = "last_name LIKE :search";
    }
    $params = [':search' => "%$search%"];
}

$orderBy = ($entity === 'sport_groups') ? 'group_name ASC' : 'last_name ASC';

$items = $repo->findAll($where, $params, $orderBy, "$limit OFFSET $offset");

В методе findAll эти параметры собираются в SQL-запрос:

$sql = 'SELECT * FROM ' . $this->tableName;
if ($where) {
    $sql .= ' WHERE ' . $where;
}
if ($orderBy) {
    $sql .= ' ORDER BY ' . $orderBy;
}
if ($limit) {
    $sql .= ' LIMIT ' . $limit;
}
$stmt = $this->pdo->prepare($sql);
$stmt->execute($params);

Таким образом, поиск по фамилии или названию реализован через WHERE, сортировка по алфавиту через ORDER BY, а пагинация через LIMIT и OFFSET. Все пользовательские данные передаются через подготовленные выражения, что исключает SQL-инъекции.

**Дата:** 20.05.2026
