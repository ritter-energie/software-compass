<?php
/**
 * @var \App\Domain\Dependency\Dependency|null $dependency
 * @var \App\Domain\Component\Component[] $components
 * @var array<int, array<string, mixed>> $dependencyTypes
 * @var array<int, array<string, mixed>> $protocols
 * @var array<int, array<string, mixed>> $statuses
 * @var array<int, array<string, mixed>> $criticalityLevels
 * @var \App\Domain\Person\Person[] $people
 */
?>
<div class="form-grid">
    <div class="form-field">
        <label for="name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.name_required')) ?></label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($dependency?->name() ?? '') ?>">
    </div>

    <div class="form-field">
        <label for="source_component_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.source_component_required')) ?></label>
        <select id="source_component_id" name="source_component_id" required>
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.select')) ?></option>
            <?php foreach ($components as $component): ?>
                <option value="<?= $component->id() ?>" <?= $dependency?->sourceComponentId() === $component->id() ? 'selected' : '' ?>><?= htmlspecialchars($component->name()) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="target_component_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.target_component_required')) ?></label>
        <select id="target_component_id" name="target_component_id" required>
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.select')) ?></option>
            <?php foreach ($components as $component): ?>
                <option value="<?= $component->id() ?>" <?= $dependency?->targetComponentId() === $component->id() ? 'selected' : '' ?>><?= htmlspecialchars($component->name()) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="dependency_type_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.type_required')) ?></label>
        <select id="dependency_type_id" name="dependency_type_id" required>
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.select')) ?></option>
            <?php foreach ($dependencyTypes as $type): ?>
                <option value="<?= $type['id'] ?>" <?= $dependency?->dependencyTypeId() === (int) $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="protocol_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.protocol')) ?></label>
        <select id="protocol_id" name="protocol_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($protocols as $protocol): ?>
                <option value="<?= $protocol['id'] ?>" <?= $dependency?->protocolId() === (int) $protocol['id'] ? 'selected' : '' ?>><?= htmlspecialchars($protocol['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="status_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.status_required')) ?></label>
        <select id="status_id" name="status_id" required>
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.select')) ?></option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status['id'] ?>" <?= $dependency?->statusId() === (int) $status['id'] ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="criticality_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.criticality')) ?></label>
        <select id="criticality_id" name="criticality_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($criticalityLevels as $level): ?>
                <option value="<?= $level['id'] ?>" <?= $dependency?->criticalityId() === (int) $level['id'] ? 'selected' : '' ?>><?= htmlspecialchars($level['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="owner_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.owner')) ?></label>
        <select id="owner_id" name="owner_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($people as $person): ?>
                <option value="<?= $person->id() ?>" <?= $dependency?->ownerId() === $person->id() ? 'selected' : '' ?>><?= htmlspecialchars($person->name()) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="frequency"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.frequency')) ?></label>
        <input type="text" id="frequency" name="frequency" value="<?= htmlspecialchars($dependency?->frequency() ?? '') ?>">
    </div>

    <div class="form-field">
        <label for="direction"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.direction')) ?></label>
        <select id="direction" name="direction">
            <option value="source_to_target" <?= $dependency?->direction() === 'source_to_target' ? 'selected' : '' ?>><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'form.direction_source_to_target',
            )) ?></option>
            <option value="target_to_source" <?= $dependency?->direction() === 'target_to_source' ? 'selected' : '' ?>><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'form.direction_target_to_source',
            )) ?></option>
        </select>
    </div>

    <div class="form-field form-field-checkbox">
        <label><input type="checkbox" name="is_bidirectional" value="1" <?= $dependency?->isBidirectional() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'form.bidirectional',
        )) ?></label>
    </div>

    <div class="form-field form-field-wide">
        <label for="description"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.description')) ?></label>
        <textarea id="description" name="description" rows="2"><?= htmlspecialchars($dependency?->description() ?? '') ?></textarea>
    </div>

    <div class="form-field form-field-wide">
        <label for="data_description"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.data_description')) ?></label>
        <textarea id="data_description" name="data_description" rows="2"><?= htmlspecialchars($dependency?->dataDescription() ?? '') ?></textarea>
        <p class="hint"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.interface_data_hint')) ?></p>
    </div>

    <div class="form-field">
        <label for="authentication_method"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.authentication_method')) ?></label>
        <input type="text" id="authentication_method" name="authentication_method" value="<?= htmlspecialchars($dependency?->authenticationMethod() ?? '') ?>">
    </div>

    <div class="form-field">
        <label for="documentation_url"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.documentation_url')) ?></label>
        <input type="url" id="documentation_url" name="documentation_url" value="<?= htmlspecialchars($dependency?->documentationUrl() ?? '') ?>">
    </div>

    <div class="form-field form-field-wide">
        <label for="technical_notes"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.technical_notes')) ?></label>
        <textarea id="technical_notes" name="technical_notes" rows="3"><?= htmlspecialchars($dependency?->technicalNotes() ?? '') ?></textarea>
    </div>
</div>
