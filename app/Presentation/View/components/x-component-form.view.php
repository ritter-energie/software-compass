<?php
/**
 * Shared form fields for both the create and edit component views.
 *
 * @var \App\Domain\Component\Component|null $component
 * @var array<int, array<string, mixed>> $componentTypes
 * @var array<int, array<string, mixed>> $statuses
 * @var array<int, array<string, mixed>> $criticalityLevels
 * @var array<int, array<string, mixed>> $environments
 * @var array<int, array<string, mixed>> $deploymentLocations
 * @var \App\Domain\Person\Person[] $people
 * @var array<int, array<string, mixed>> $teams
 * @var \App\Domain\Component\Component[] $availableComponents
 */
$selectedParentId = $component?->parentComponentId();
$selectedChildIds = array_flip($component?->childComponentIds() ?? []);
$personOptions = array_map(static fn (\App\Domain\Person\Person $person): array => ['id' => $person->id(), 'name' => $person->name()], $people);
$teamOptions = array_map(static fn (array $team): array => ['id' => (int) $team['id'], 'name' => (string) $team['name']], $teams);
?>
<div class="form-grid">
    <div class="form-field">
        <label for="name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.name_required')) ?></label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($component?->name() ?? '') ?>">
    </div>

    <div class="form-field">
        <label for="short_name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.short_name')) ?></label>
        <input type="text" id="short_name" name="short_name" value="<?= htmlspecialchars($component?->shortName() ?? '') ?>">
    </div>

    <div class="form-field">
        <label for="component_type_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.type_required')) ?></label>
        <select id="component_type_id" name="component_type_id" required>
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.select')) ?></option>
            <?php foreach ($componentTypes as $type): ?>
                <option value="<?= $type['id'] ?>" <?= $component?->componentTypeId() === (int) $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="status_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.status_required')) ?></label>
        <select id="status_id" name="status_id" required>
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.select')) ?></option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status['id'] ?>" <?= $component?->statusId() === (int) $status['id'] ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="criticality_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.criticality')) ?></label>
        <select id="criticality_id" name="criticality_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($criticalityLevels as $level): ?>
                <option value="<?= $level['id'] ?>" <?= $component?->criticalityId() === (int) $level['id'] ? 'selected' : '' ?>><?= htmlspecialchars($level['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="business_owner_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.business_owner')) ?></label>
        <select id="business_owner_id" name="business_owner_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($personOptions as $person): ?>
                <option value="<?= $person['id'] ?>" <?= $component?->businessOwnerId() === $person['id'] ? 'selected' : '' ?>><?= htmlspecialchars($person['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <p class="hint"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.governance_owner_hint')) ?></p>
    </div>

    <div class="form-field">
        <label for="business_owner_team_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.business_owner_team')) ?></label>
        <select id="business_owner_team_id" name="business_owner_team_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($teamOptions as $team): ?>
                <option value="<?= $team['id'] ?>" <?= $component?->businessOwnerTeamId() === $team['id'] ? 'selected' : '' ?>><?= htmlspecialchars($team['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field">
        <label for="technical_owner_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.technical_owner')) ?></label>
        <select id="technical_owner_id" name="technical_owner_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($personOptions as $person): ?>
                <option value="<?= $person['id'] ?>" <?= $component?->technicalOwnerId() === $person['id'] ? 'selected' : '' ?>><?= htmlspecialchars($person['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="technical_owner_team_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.technical_owner_team')) ?></label>
        <select id="technical_owner_team_id" name="technical_owner_team_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($teamOptions as $team): ?>
                <option value="<?= $team['id'] ?>" <?= $component?->technicalOwnerTeamId() === $team['id'] ? 'selected' : '' ?>><?= htmlspecialchars($team['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field">
        <label for="deployment_location_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.deployment_location')) ?></label>
        <select id="deployment_location_id" name="deployment_location_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($deploymentLocations as $location): ?>
                <option value="<?= $location['id'] ?>" <?= $component?->deploymentLocationId() === (int) $location['id'] ? 'selected' : '' ?>><?= htmlspecialchars(
                    $location['name'],
                ) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="environment_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.environment')) ?></label>
        <select id="environment_id" name="environment_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($environments as $environment): ?>
                <option value="<?= $environment['id'] ?>" <?= $component?->environmentId() === (int) $environment['id'] ? 'selected' : '' ?>><?= htmlspecialchars(
                    $environment['name'],
                ) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label for="project_name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.project_name')) ?></label>
        <input type="text" id="project_name" name="project_name" value="<?= htmlspecialchars($component?->projectName() ?? '') ?>">
    </div>

    <div class="form-field">
        <label for="started_on"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.started_on')) ?></label>
        <input type="date" id="started_on" name="started_on" value="<?= htmlspecialchars($component?->startedOn()?->format('Y-m-d') ?? '') ?>">
    </div>

    <div class="form-field" data-field="vendor">
        <label for="vendor"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.vendor')) ?></label>
        <input type="text" id="vendor" name="vendor" value="<?= htmlspecialchars($component?->vendor() ?? '') ?>">
    </div>

    <div class="form-field form-field-checkbox">
        <label><input type="checkbox" name="is_external" value="1" <?= $component?->isExternal() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'form.external_component',
        )) ?></label>
    </div>

    <div class="form-field form-field-wide">
        <label for="parent_component_id"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.parent_components')) ?></label>
        <select id="parent_component_id" name="parent_component_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none_option')) ?></option>
            <?php foreach ($availableComponents as $availableComponent): ?>
                <?php if ($component?->id() === $availableComponent->id()) {
                    continue;
                } ?>
                <option value="<?= $availableComponent->id() ?>" <?= $selectedParentId === (int) $availableComponent->id() ? 'selected' : '' ?>><?= htmlspecialchars($availableComponent->name()) ?></option>
            <?php endforeach; ?>
        </select>
        <p class="hint"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.parent_components_hint')) ?></p>
    </div>

    <div class="form-field form-field-wide">
        <label for="child_component_ids"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.child_components')) ?></label>
        <select id="child_component_ids" name="child_component_ids[]" multiple size="6">
            <?php foreach ($availableComponents as $availableComponent): ?>
                <?php if ($component?->id() === $availableComponent->id()) {
                    continue;
                } ?>
                <option value="<?= $availableComponent->id() ?>" <?= isset($selectedChildIds[(int) $availableComponent->id()]) ? 'selected' : '' ?>><?= htmlspecialchars($availableComponent->name()) ?></option>
            <?php endforeach; ?>
        </select>
        <p class="hint"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.child_components_hint')) ?></p>
    </div>

    <div class="form-field form-field-wide">
        <label for="purpose"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.purpose')) ?></label>
        <textarea id="purpose" name="purpose" rows="2"><?= htmlspecialchars($component?->purpose() ?? '') ?></textarea>
    </div>

    <div class="form-field form-field-wide">
        <label for="description"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.description')) ?></label>
        <textarea id="description" name="description" rows="3"><?= htmlspecialchars($component?->description() ?? '') ?></textarea>
    </div>

    <div class="form-field">
        <label for="documentation_url"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.documentation_url')) ?></label>
        <input type="url" id="documentation_url" name="documentation_url" value="<?= htmlspecialchars($component?->documentationUrl() ?? '') ?>">
    </div>

    <div class="form-field">
        <label for="repository_url"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.repository_url')) ?></label>
        <input type="url" id="repository_url" name="repository_url" value="<?= htmlspecialchars($component?->repositoryUrl() ?? '') ?>">
    </div>

    <div class="form-field form-field-wide">
        <label for="lifecycle_notes"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.lifecycle_notes')) ?></label>
        <textarea id="lifecycle_notes" name="lifecycle_notes" rows="2"><?= htmlspecialchars($component?->lifecycleNotes() ?? '') ?></textarea>
    </div>
</div>
