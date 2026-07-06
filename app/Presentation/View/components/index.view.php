<?php
/**
 * @var \App\Domain\Component\Component[] $components
 * @var \App\Domain\Component\ComponentSearchCriteria $criteria
 * @var array<int, array<string, mixed>> $componentTypes
 * @var array<int, array<string, mixed>> $statuses
 * @var array<int, array<string, mixed>> $criticalityLevels
 * @var array<int, array<string, mixed>> $environments
 * @var \App\Domain\Person\Person[] $people
 * @var array<int, array<string, mixed>> $teams
 */
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.description')) ?></p>
        </div>
        <a class="button-primary" href="/components/create">+ <?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.new')) ?></a>
    </div>

    <form class="filter-bar" method="GET" action="/components">
        <input type="text" name="q" placeholder="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.search_components')) ?>" value="<?= htmlspecialchars(
            $criteria->query ?? '',
        ) ?>">

        <select name="component_type_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_types')) ?></option>
            <?php foreach ($componentTypes as $type): ?>
                <option value="<?= $type['id'] ?>" <?= $criteria->componentTypeId === (int) $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_statuses')) ?></option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status['id'] ?>" <?= $criteria->statusId === (int) $status['id'] ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="criticality_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_criticalities')) ?></option>
            <?php foreach ($criticalityLevels as $level): ?>
                <option value="<?= $level['id'] ?>" <?= $criteria->criticalityId === (int) $level['id'] ? 'selected' : '' ?>><?= htmlspecialchars($level['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="environment_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_environments')) ?></option>
            <?php foreach ($environments as $environment): ?>
                <option value="<?= $environment['id'] ?>" <?= $criteria->environmentId === (int) $environment['id'] ? 'selected' : '' ?>><?= htmlspecialchars($environment['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="owner_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_owners')) ?></option>
            <?php foreach ($people as $person): ?>
                <option value="<?= $person->id() ?>" <?= $criteria->ownerId === $person->id() ? 'selected' : '' ?>><?= htmlspecialchars($person->name()) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="owner_team_id">
            <option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_owner_teams')) ?></option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['id'] ?>" <?= $criteria->ownerTeamId === (int) $team['id'] ? 'selected' : '' ?>><?= htmlspecialchars($team['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="checkbox-inline">
            <input type="checkbox" name="is_external" value="1" <?= $criteria->isExternal === true ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'filter.external_only',
            )) ?>
        </label>

        <button type="submit" class="button-secondary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.filter')) ?></button>
        <a href="/components"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.reset')) ?></a>
    </form>

    <table class="data-table">
        <thead>
            <tr>
                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th>
                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.type')) ?></th>
                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status')) ?></th>
                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.criticality')) ?></th>
                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.purpose')) ?></th>
                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.external')) ?></th>
                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.actions')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($components === []): ?>
            <tr><td colspan="7"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_components_match')) ?></td></tr>
        <?php endif; ?>
        <?php foreach ($components as $component): ?>
            <tr>
                <td><a href="/components/<?= $component->id() ?>"><?= htmlspecialchars($component->name()) ?></a></td>
                <td><?= $component->componentTypeId() ?></td>
                <td><?= $component->statusId() ?></td>
                <td><?= $component->criticalityId() ?? '—' ?></td>
                <td><?= htmlspecialchars((string) $component->purpose()) ?></td>
                <td><?= htmlspecialchars(\App\Shared\Support\Translator::translate($component->isExternal() ? 'common.yes' : 'common.no')) ?></td>
                <td class="actions">
                    <a href="/components/<?= $component->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.view')) ?></a>
                    <a href="/components/<?= $component->id() ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.edit')) ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</x-layout>
