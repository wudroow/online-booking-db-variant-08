<p>Вы уверены, что хотите удалить запись <strong><?= htmlspecialchars($item['last_name'] ?? $item['group_name'] ?? '') ?></strong>?</p>

<form method="POST">
    <div class="form-buttons">
        <button type="submit" class="btn btn-danger">Да, удалить</button>
        <a href="index.php?entity=<?= $entity ?>&action=list" class="btn">Отмена</a>
    </div>
</form>