<?php
class CrudController
{
    private $repositories = [
        'athletes' => 'AthleteRepository',
        'coaches' => 'CoachRepository',
        'sport_groups' => 'CoachRepository'
    ];
    
    private $titles = [
        'athletes' => 'Спортсмены',
        'coaches' => 'Тренеры',
        'sport_groups' => 'Тренировочные группы'
    ];

    public function handle($entity, $action)
    {
        $pdo = Database::getInstance()->getConnection();
        $repoClass = $this->repositories[$entity] ?? null;
        if (!$repoClass) {
            die('Неизвестная сущность');
        }
        $repo = new $repoClass($pdo);
        
        switch ($action) {
            case 'list':
                $this->listAction($repo, $entity, $action);
                break;
            case 'create':
                $this->createAction($repo, $entity, $action);
                break;
            case 'edit':
                $this->editAction($repo, $entity, $action);
                break;
            case 'delete':
                $this->deleteAction($repo, $entity, $action);
                break;
            case 'view':
                $this->viewAction($repo, $entity, $action);
                break;
            default:
                $this->listAction($repo, $entity, 'list');
        }
    }
    
    private function listAction($repo, $entity, $action)
    {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $where = '';
        $params = [];
        if ($search) {
            $where = "last_name LIKE :search";
            $params = [':search' => "%$search%"];
        }
        
        $items = $repo->findAll($where, $params, 'last_name ASC', "$limit OFFSET $offset");
        $total = count($repo->findAll($where, $params));
        $totalPages = ceil($total / $limit);
        
        $title = $this->titles[$entity] ?? $entity;
        $isEdit = false; // Добавлено, чтобы избежать Warning в форме, если она как-то затронута в списке
        require_once 'views/layout.php';
    }
    
    private function createAction($repo, $entity, $action)
    {
        $errors = [];
        $data = $_POST;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // базовая валидация
            if (empty($data['last_name'])) $errors['last_name'] = 'Фамилия обязательна';
            if (empty($data['first_name'])) $errors['first_name'] = 'Имя обязательно';
            
            if (empty($errors)) {
                try {
                    $repo->create($data);
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Запись создана'];
                    header("Location: index.php?entity=$entity&action=list");
                    exit;
                } catch (Exception $e) {
                    $errors['general'] = $e->getMessage();
                }
            }
        }
        
        $title = $this->titles[$entity] ?? $entity;
        $isEdit = false; // Явно передаем, что это создание, а не редактирование
        require_once 'views/layout.php';
    }
    
    private function editAction($repo, $entity, $action)
    {
        $id = $_GET['id'] ?? 0;
        $item = $repo->findById($id);
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            if (empty($data['last_name'])) $errors['last_name'] = 'Фамилия обязательна';
            if (empty($data['first_name'])) $errors['first_name'] = 'Имя обязательно';
            
            if (empty($errors)) {
                try {
                    // update (нужно добавить метод в репозитории)
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Запись обновлена'];
                    header("Location: index.php?entity=$entity&action=list");
                    exit;
                } catch (Exception $e) {
                    $errors['general'] = $e->getMessage();
                }
            }
            $item = array_merge($item, $data);
        }
        
        $title = $this->titles[$entity] ?? $entity;
        $isEdit = true; // Явно передаем, что это режим редактирования
        require_once 'views/layout.php';
    }
    
    private function deleteAction($repo, $entity, $action)
    {
        $id = $_GET['id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $repo->delete($id);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Запись удалена'];
                header("Location: index.php?entity=$entity&action=list");
                exit;
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
                header("Location: index.php?entity=$entity&action=list");
                exit;
            }
        }
        
        $item = $repo->findById($id);
        $title = $this->titles[$entity] ?? $entity;
        $isEdit = false;
        require_once 'views/layout.php';
    }
    
    private function viewAction($repo, $entity, $action)
    {
        $id = $_GET['id'] ?? 0;
        $item = $repo->findById($id);
        $title = $this->titles[$entity] ?? $entity;
        $isEdit = false;
        require_once 'views/layout.php';
    }
}
