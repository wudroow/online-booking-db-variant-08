<div class="actions">
    <a href="index.php?entity=<?= $entity ?>&action=create" class="btn btn-primary">+ Добавить</a>
    <form method="GET" class="search-form">
        <input type="hidden" name="entity" value="<?= $entity ?>">
        <input type="hidden" name="action" value="list">
        <input type="text" name="search" placeholder="Поиск по фамилии" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn">Найти</button>
    </form>
</div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Телефон</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['athlete_id'] ?? $item['coach_id'] ?? $item['group_id']) ?></td>
            <td><?= htmlspecialchars($item['last_name'] ?? $item['group_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($item['first_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($item['phone'] ?? '-') ?></td>
            <td class="actions-cell">
                <a href="index.php?entity=<?= $entity ?>&action=view&id=<?= $item['athlete_id'] ?? $item['coach_id'] ?? $item['group_id'] ?>" class="btn-sm">👁️</a>
                <a href="index.php?entity=<?= $entity ?>&action=edit&id=<?= $item['athlete_id'] ?? $item['coach_id'] ?? $item['group_id'] ?>" class="btn-sm">✏️</a>
                <a href="index.php?entity=<?= $entity ?>&action=delete&id=<?= $item['athlete_id'] ?? $item['coach_id'] ?? $item['group_id'] ?>" class="btn-sm btn-danger">🗑️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="index.php?entity=<?= $entity ?>&action=list&page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>