<?php
/**
 * @var \App\Domain\Dependency\Dependency[] $dependencies
 * @var \App\Domain\Dependency\DependencySearchCriteria $criteria
 * @var \App\Domain\Component\Component[] $components
 * @var array<int, array<string, mixed>> $dependencyTypes
 * @var array<int, array<string, mixed>> $protocols
 * @var array<int, array<string, mixed>> $statuses
 * @var array<int, array<string, mixed>> $criticalityLevels
 * @var \App\Domain\Person\Person[] $people
 * @var array<int, array<string, mixed>> $dataObjects
 */
$componentName = static function (int $id) use ($components): string {
    foreach ($components as $component) {
        if ($component->id() === $id) {
            return $component->name();
        }
    }

    return 'C' . $id;
};
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.description')) ?></p>
        </div>
        <a class="button-primary" href="/dependencies/create">+ <?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.new')) ?></a>
    </div>

    <form class="filter-bar" method="GET" action="/dependencies">
        <input type="text" name="q" placeholder="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.search_dependencies')) ?>" value="<?= htmlspecialchars($criteria->query ?? '') ?>">

        <select name="source_component_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_sources')) ?></option><?php foreach ($components as $component): ?><option value="<?= $component->id() ?>" <?= $criteria->sourceComponentId === $component->id() ? 'selected' : '' ?>><?= htmlspecialchars($component->name()) ?></option><?php endforeach; ?></select>
        <select name="target_component_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_targets')) ?></option><?php foreach ($components as $component): ?><option value="<?= $component->id() ?>" <?= $criteria->targetComponentId === $component->id() ? 'selected' : '' ?>><?= htmlspecialchars($component->name()) ?></option><?php endforeach; ?></select>
        <select name="dependency_type_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_types')) ?></option><?php foreach ($dependencyTypes as $type): ?><option value="<?= $type['id'] ?>" <?= $criteria->dependencyTypeId === (int) $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option><?php endforeach; ?></select>
        <select name="protocol_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_protocols')) ?></option><?php foreach ($protocols as $protocol): ?><option value="<?= $protocol['id'] ?>" <?= $criteria->protocolId === (int) $protocol['id'] ? 'selected' : '' ?>><?= htmlspecialchars($protocol['name']) ?></option><?php endforeach; ?></select>
        <select name="status_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_statuses')) ?></option><?php foreach ($statuses as $status): ?><option value="<?= $status['id'] ?>" <?= $criteria->statusId === (int) $status['id'] ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?></option><?php endforeach; ?></select>
        <select name="criticality_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_criticalities')) ?></option><?php foreach ($criticalityLevels as $level): ?><option value="<?= $level['id'] ?>" <?= $criteria->criticalityId === (int) $level['id'] ? 'selected' : '' ?>><?= htmlspecialchars($level['name']) ?></option><?php endforeach; ?></select>
        <select name="owner_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_owners')) ?></option><?php foreach ($people as $person): ?><option value="<?= $person->id() ?>" <?= $criteria->ownerId === $person->id() ? 'selected' : '' ?>><?= htmlspecialchars($person->name()) ?></option><?php endforeach; ?></select>
        <select name="data_object_id"><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('filter.all_data_objects')) ?></option><?php foreach ($dataObjects as $object): ?><option value="<?= $object['id'] ?>" <?= $criteria->dataObjectId === (int) $object['id'] ? 'selected' : '' ?>><?= htmlspecialchars($object['name']) ?></option><?php endforeach; ?></select>

        <button type="submit" class="button-secondary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.filter')) ?></button>
        <a href="/dependencies"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.reset')) ?></a>
    </form>

    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.source')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.target')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.data')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.frequency')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.quality')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.actions')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($dependencies as $dependency): ?>
                <tr>
                    <td><a href="/dependencies/<?= $dependency->id() ?>"><?= htmlspecialchars($dependency->name()) ?></a></td>
                    <td><?= htmlspecialchars($componentName($dependency->sourceComponentId())) ?></td>
                    <td><?= htmlspecialchars($componentName($dependency->targetComponentId())) ?></td>
                    <td><?= htmlspecialchars((string) $dependency->dataDescription()) ?: '—' ?></td>
                    <td><?= htmlspecialchars((string) $dependency->frequency()) ?: '—' ?></td>
                    <td><?= $dependency->isIncomplete() ? '<span class="badge badge-warning">' . htmlspecialchars(\App\Shared\Support\Translator::translate('common.incomplete')) . '</span>' : '<span class="badge badge-success">' . htmlspecialchars(\App\Shared\Support\Translator::translate('common.complete')) . '</span>' ?></td>
                    <td class="actions"><a href="/dependencies/<?= $dependency->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.view')) ?></a><a href="/dependencies/<?= $dependency->id() ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.edit')) ?></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($dependencies === []): ?><tr><td colspan="7"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_dependencies_match')) ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</x-layout>
