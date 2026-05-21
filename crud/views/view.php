<div class="card">
    <p><strong>ID:</strong> <?= htmlspecialchars($item['athlete_id'] ?? $item['coach_id'] ?? $item['group_id']) ?></p>
    <p><strong>Фамилия:</strong> <?= htmlspecialchars($item['last_name'] ?? $item['group_name'] ?? '-') ?></p>
    <p><strong>Имя:</strong> <?= htmlspecialchars($item['first_name'] ?? '-') ?></p>
    <p><strong>Телефон:</strong> <?= htmlspecialchars($item['phone'] ?? '-') ?></p>
    
    <a href="index.php?entity=<?= $entity ?>&action=list" class="btn">← Назад к списку</a>
</div>