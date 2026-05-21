<form method="POST" class="form">
    <div class="form-group">
        <label>Фамилия *</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($data['last_name'] ?? $item['last_name'] ?? '') ?>" required>
        <?php if (isset($errors['last_name'])): ?>
            <span class="error"><?= $errors['last_name'] ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label>Имя *</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($data['first_name'] ?? $item['first_name'] ?? '') ?>" required>
        <?php if (isset($errors['first_name'])): ?>
            <span class="error"><?= $errors['first_name'] ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label>Телефон</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($data['phone'] ?? $item['phone'] ?? '') ?>">
    </div>
    
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Сохранить' : 'Создать' ?></button>
        <a href="index.php?entity=<?= $entity ?>&action=list" class="btn">Отмена</a>
    </div>
</form>