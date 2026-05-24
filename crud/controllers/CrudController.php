<?php
class CrudController
{
    private $pdo;
    private $repositories = [
        'athletes' => 'AthleteRepository',
        'coaches' => 'CoachRepository',
        'sport_groups' => 'SportGroupRepository'
    ];
    
    private $titles = [
        'athletes' => 'Спортсмены',
        'coaches' => 'Тренеры',
        'sport_groups' => 'Услуги'
    ];

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function handle($entity, $action)
    {
        switch ($action) {
            case 'list':
                $this->listAction($entity);
                break;
            case 'create':
                $this->createAction($entity);
                break;
            case 'edit':
                $id = $_GET['id'] ?? 0;
                $this->editAction($entity, $id);
                break;
            case 'delete':
                $id = $_GET['id'] ?? 0;
                $this->deleteAction($entity, $id);
                break;
            case 'view':
                $id = $_GET['id'] ?? 0;
                $this->viewAction($entity, $id);
                break;
            default:
                $this->listAction($entity);
        }
    }

    public function listAction($entity)
    {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $repo = $this->getRepository($entity);
        
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
        $total = count($repo->findAll($where, $params));
        $totalPages = ceil($total / $limit);
        
        $title = $this->titles[$entity] ?? $entity;
        $action = 'list';
        require_once 'views/layout.php';
    }

    public function createAction($entity)
    {
        $errors = [];
        $data = $_POST;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($entity === 'sport_groups') {
                if (empty($data['group_name'])) $errors['group_name'] = 'Название группы обязательно';
            } else {
                if (empty($data['last_name'])) $errors['last_name'] = 'Фамилия обязательна';
                if (empty($data['first_name'])) $errors['first_name'] = 'Имя обязательно';
            }
            
            if (empty($errors)) {
                try {
                    $repo = $this->getRepository($entity);
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
        $action = 'create';
        $isEdit = false;
        require_once 'views/layout.php';
    }

    public function editAction($entity, $id)
    {
        $repo = $this->getRepository($entity);
        $item = $repo->findById($id);
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            if ($entity === 'sport_groups') {
                if (empty($data['group_name'])) $errors['group_name'] = 'Название группы обязательно';
            } else {
                if (empty($data['last_name'])) $errors['last_name'] = 'Фамилия обязательна';
                if (empty($data['first_name'])) $errors['first_name'] = 'Имя обязательно';
            }
            
            if (empty($errors)) {
                try {
                    if ($entity === 'sport_groups') {
                        $sql = "UPDATE sport_groups SET group_name = :group_name, current_capacity = :current_capacity WHERE group_id = :id";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([
                            ':group_name' => $data['group_name'],
                            ':current_capacity' => $data['current_capacity'] ?? 0,
                            ':id' => $id
                        ]);
                    } else {
                        $sql = "UPDATE " . $this->getTableName($entity) . " SET last_name = :last_name, first_name = :first_name, phone = :phone WHERE " . $this->getPrimaryKey($entity) . " = :id";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([
                            ':last_name' => $data['last_name'],
                            ':first_name' => $data['first_name'],
                            ':phone' => $data['phone'] ?? null,
                            ':id' => $id
                        ]);
                    }
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
        $action = 'edit';
        $isEdit = true;
        require_once 'views/layout.php';
    }

    public function deleteAction($entity, $id)
    {
        $repo = $this->getRepository($entity);
        
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
        $action = 'delete';
        require_once 'views/layout.php';
    }

    public function viewAction($entity, $id)
    {
        $repo = $this->getRepository($entity);
        $item = $repo->findById($id);
        $title = $this->titles[$entity] ?? $entity;
        $action = 'view';
        require_once 'views/layout.php';
    }

    private function getRepository($entity)
    {
        $repoClass = $this->repositories[$entity] ?? null;
        if (!$repoClass) {
            throw new Exception('Неизвестная сущность: ' . $entity);
        }
        return new $repoClass($this->pdo);
    }

    private function getTableName($entity)
    {
        if ($entity === 'athletes') return 'athletes';
        if ($entity === 'coaches') return 'coaches';
        return 'sport_groups';
    }

    private function getPrimaryKey($entity)
    {
        if ($entity === 'athletes') return 'athlete_id';
        if ($entity === 'coaches') return 'coach_id';
        return 'group_id';
    }
}
