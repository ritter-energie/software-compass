<?php
/**
 * @var \App\Domain\ReferenceData\ReferenceDataType $type
 * @var \App\Domain\ReferenceData\ReferenceDataEntry|null $entry
 */
?>
<div class="form-grid">
    <?php foreach ($type->fields() as $field): ?>
        <?php $value = $entry?->value($field); ?>
        <?php if ($field->type() === \App\Domain\ReferenceData\ReferenceDataFieldType::BOOLEAN): ?>
            <div class="form-field form-field-checkbox">
                <label>
                    <input type="checkbox" name="<?= htmlspecialchars($field->value) ?>" value="1" <?= (bool) $value ? 'checked' : '' ?>>
                    <?= htmlspecialchars(\App\Shared\Support\Translator::translate($field->labelKey())) ?>
                </label>
            </div>
        <?php elseif ($field->type() === \App\Domain\ReferenceData\ReferenceDataFieldType::TEXTAREA): ?>
            <div class="form-field form-field-wide">
                <label for="<?= htmlspecialchars($field->value) ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate($field->labelKey())) ?></label>
                <textarea id="<?= htmlspecialchars($field->value) ?>" name="<?= htmlspecialchars($field->value) ?>" rows="3" <?= $field->isRequired() ? 'required' : '' ?>><?= htmlspecialchars((string) ($value ?? '')) ?></textarea>
            </div>
        <?php else: ?>
            <div class="form-field">
                <label for="<?= htmlspecialchars($field->value) ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate($field->labelKey())) ?></label>
                <input
                    type="<?= $field->type() === \App\Domain\ReferenceData\ReferenceDataFieldType::NUMBER ? 'number' : 'text' ?>"
                    id="<?= htmlspecialchars($field->value) ?>"
                    name="<?= htmlspecialchars($field->value) ?>"
                    value="<?= htmlspecialchars((string) ($value ?? '')) ?>"
                    <?= $field->isRequired() ? 'required' : '' ?>
                >
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>


