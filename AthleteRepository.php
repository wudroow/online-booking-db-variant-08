<?php
/**
 * Репозиторий для работы со спортсменами
 */
class AthleteRepository extends AbstractRepository
{
    protected $tableName = 'athletes';
    protected $primaryKey = 'athlete_id';

    /**
     * Найти спортсмена по телефону родителя
     */
    public function findByParentPhone($parentPhone)
    {
        $sql = 'SELECT * FROM athletes WHERE parent_phone = :parent_phone';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':parent_phone' => $parentPhone]);
        return $stmt->fetchAll();
    }

    /**
     * Получить рейтинг спортсменов по достижениям
     */
    public function getRatingByAchievements()
    {
        $sql = 'SELECT 
                    athlete_id,
                    last_name,
                    first_name,
                    achievement_count,
                    RANK() OVER (ORDER BY achievement_count DESC) AS rating_rank
                FROM (
                    SELECT 
                        a.athlete_id,
                        a.last_name,
                        a.first_name,
                        COUNT(ach.achievement_id) AS achievement_count
                    FROM athletes a
                    LEFT JOIN achievements ach ON a.athlete_id = ach.athlete_id
                    GROUP BY a.athlete_id
                ) AS athlete_stats
                ORDER BY rating_rank';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Создать нового спортсмена
     */
    public function create($data)
    {
        $sql = 'INSERT INTO athletes (last_name, first_name, patronymic, birth_date, phone, parent_phone, parent_email, medical_note)
                VALUES (:last_name, :first_name, :patronymic, :birth_date, :phone, :parent_phone, :parent_email, :medical_note)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':last_name' => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':patronymic' => $data['patronymic'] ?? null,
            ':birth_date' => $data['birth_date'],
            ':phone' => $data['phone'] ?? null,
            ':parent_phone' => $data['parent_phone'],
            ':parent_email' => $data['parent_email'] ?? null,
            ':medical_note' => $data['medical_note'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }
}