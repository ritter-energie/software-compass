<?php
/**
 * @var \App\Domain\Journey\Journey|null $journey
 * @var array<int, array<string, mixed>> $statuses
 * @var \App\Domain\Person\Person[] $people
 */
?>
<div class="form-grid">
    <div class="form-field">
        <label for="name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.name_required')) ?></label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($journey?->name() ?? '') ?>">
    </div>
    <div class="form-field">
        <label for="status_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.status_required')) ?></label>
        <select id="status_id" name="status_id" required>
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.select')) ?></option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status['id'] ?>" <?= $journey?->statusId() === (int) $status['id'] ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field">
        <label for="owner_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.owner')) ?></label>
        <select id="owner_id" name="owner_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($people as $person): ?>
                <option value="<?= $person->id() ?>" <?= $journey?->ownerId() === $person->id() ? 'selected' : '' ?>><?= htmlspecialchars($person->name()) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field">
        <label for="sort_order"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.sort_order')) ?></label>
        <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars((string) ($journey?->sortOrder() ?? 0)) ?>">
    </div>
    <div class="form-field form-field-wide">
        <label for="description"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.description')) ?></label>
        <textarea id="description" name="description" rows="3"><?= htmlspecialchars($journey?->description() ?? '') ?></textarea>
    </div>
</div>

