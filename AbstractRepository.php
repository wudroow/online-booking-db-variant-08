<?php
/**
 * Абстрактный базовый репозиторий с общими методами
 */
abstract class AbstractRepository
{
    protected $pdo;
    protected $tableName;
    protected $primaryKey = 'id';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Найти все записи
     */
    public function findAll($where = '', $params = [], $orderBy = '', $limit = '')
    {
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
        return $stmt->fetchAll();
    }

    /**
     * Найти запись по ID
     */
    public function findById($id)
    {
        $sql = 'SELECT * FROM ' . $this->tableName . ' WHERE ' . $this->primaryKey . ' = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        if (!$result) {
            throw new RepositoryException('Запись с ID ' . $id . ' не найдена в таблице ' . $this->tableName);
        }
        return $result;
    }

    /**
     * Удалить запись по ID
     */
    public function delete($id)
    {
        $sql = 'DELETE FROM ' . $this->tableName . ' WHERE ' . $this->primaryKey . ' = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}