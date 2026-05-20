<?php
/**
 * Репозиторий для работы с тренерами
 */
class CoachRepository extends AbstractRepository
{
    protected $tableName = 'coaches';
    protected $primaryKey = 'coach_id';

    /**
     * Найти тренера по телефону
     */
    public function findByPhone($phone)
    {
        $sql = 'SELECT * FROM coaches WHERE phone = :phone';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':phone' => $phone]);
        return $stmt->fetch();
    }

    /**
     * Получить тренеров с заполняемостью групп более 80%
     */
    public function getCoachesWithHighFillRate($threshold = 80)
    {
        $sql = 'SELECT 
                    c.coach_id,
                    c.last_name,
                    c.first_name,
                    AVG(g.current_capacity / c.max_group_capacity * 100) AS avg_fill_percentage
                FROM coaches c
                JOIN sport_groups g ON c.coach_id = g.coach_id
                GROUP BY c.coach_id
                HAVING avg_fill_percentage > :threshold
                ORDER BY avg_fill_percentage DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':threshold' => $threshold]);
        return $stmt->fetchAll();
    }

    /**
     * Создать нового тренера
     */
    public function create($data)
    {
        $sql = 'INSERT INTO coaches (last_name, first_name, patronymic, phone, specialization, hire_date, max_group_capacity)
                VALUES (:last_name, :first_name, :patronymic, :phone, :specialization, :hire_date, :max_group_capacity)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':last_name' => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':patronymic' => $data['patronymic'] ?? null,
            ':phone' => $data['phone'],
            ':specialization' => $data['specialization'],
            ':hire_date' => $data['hire_date'],
            ':max_group_capacity' => $data['max_group_capacity']
        ]);
        return $this->pdo->lastInsertId();
    }
}