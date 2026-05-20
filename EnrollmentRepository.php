<?php
/**
 * Репозиторий для работы с записями на тренировки
 */
class EnrollmentRepository extends AbstractRepository
{
    protected $tableName = 'enrollments';
    protected $primaryKey = 'enrollment_id';

    /**
     * Получить все записи с подробной информацией (JOIN)
     */
    public function getEnrollmentsWithDetails()
    {
        $sql = 'SELECT 
                    a.last_name AS athlete_last_name,
                    a.first_name AS athlete_first_name,
                    ag.group_name AS age_group,
                    c.last_name AS coach_last_name,
                    e.enrollment_date,
                    e.enrollment_reason,
                    e.status
                FROM enrollments e
                JOIN athletes a ON e.athlete_id = a.athlete_id
                JOIN sport_groups g ON e.group_id = g.group_id
                JOIN coaches c ON g.coach_id = c.coach_id
                JOIN age_groups ag ON g.age_group_id = ag.age_group_id
                ORDER BY e.enrollment_date DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Найти записи спортсмена
     */
    public function findByAthleteId($athleteId)
    {
        $sql = 'SELECT * FROM enrollments WHERE athlete_id = :athlete_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':athlete_id' => $athleteId]);
        return $stmt->fetchAll();
    }

    /**
     * Создать новую запись (с транзакцией для обновления вместимости группы)
     */
    public function create($data)
    {
        try {
            $this->pdo->beginTransaction();

            $checkSql = 'SELECT COUNT(*) FROM enrollments 
                         WHERE athlete_id = :athlete_id AND group_id = :group_id AND status = "записана"';
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([
                ':athlete_id' => $data['athlete_id'],
                ':group_id' => $data['group_id']
            ]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new RepositoryException('Спортсмен уже записан в эту группу');
            }

            $sql = 'INSERT INTO enrollments (athlete_id, group_id, enrollment_date, status, enrollment_reason)
                    VALUES (:athlete_id, :group_id, :enrollment_date, :status, :enrollment_reason)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':athlete_id' => $data['athlete_id'],
                ':group_id' => $data['group_id'],
                ':enrollment_date' => $data['enrollment_date'],
                ':status' => $data['status'] ?? 'записана',
                ':enrollment_reason' => $data['enrollment_reason']
            ]);

            $updateSql = 'UPDATE sport_groups SET current_capacity = current_capacity + 1 
                          WHERE group_id = :group_id';
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([':group_id' => $data['group_id']]);

            $this->pdo->commit();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RepositoryException('Ошибка при создании записи: ' . $e->getMessage());
        } catch (RepositoryException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Отменить запись (с транзакцией для уменьшения вместимости)
     */
    public function cancel($enrollmentId)
    {
        try {
            $this->pdo->beginTransaction();

            $sql = 'SELECT group_id FROM enrollments WHERE enrollment_id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $enrollmentId]);
            $enrollment = $stmt->fetch();
            $groupId = $enrollment['group_id'];

            $updateSql = 'UPDATE enrollments SET status = "отчислена" WHERE enrollment_id = :id';
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([':id' => $enrollmentId]);

            $capacitySql = 'UPDATE sport_groups SET current_capacity = current_capacity - 1 
                            WHERE group_id = :group_id AND current_capacity > 0';
            $capacityStmt = $this->pdo->prepare($capacitySql);
            $capacityStmt->execute([':group_id' => $groupId]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RepositoryException('Ошибка при отмене записи: ' . $e->getMessage());
        }
    }
}