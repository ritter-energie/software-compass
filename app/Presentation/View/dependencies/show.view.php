<?php
/**
 * @var \App\Domain\Dependency\Dependency|null $dependency
 * @var \App\Domain\Component\Component[] $components
 * @var \App\Domain\Person\Person[] $people
 */
$componentName = static function (int $id) use ($components): string {
    foreach ($components as $component) {
        if ($component->id() === $id) {
            return $component->name();
        }
    }

    return 'C' . $id;
};
$personName = static function (?int $id) use ($people): string {
    if ($id === null) {
        return '—';
    }
    foreach ($people as $person) {
        if ($person->id() === $id) {
            return $person->name();
        }
    }

    return '—';
};

$t = static fn (string $key, mixed ...$arguments): string => \App\Shared\Support\Translator::translate($key, ...$arguments);
?>
<x-layout>
    <?php if ($dependency === null): ?>
        <h2><?= htmlspecialchars($t('dependencies.not_found')) ?></h2>
        <p><a href="/dependencies"><?= htmlspecialchars($t('dependencies.back_to_list')) ?></a></p>
    <?php else: ?>
        <div class="page-header">
            <h2><?= htmlspecialchars($dependency->name()) ?></h2>
            <div class="actions"><a class="button-secondary" href="/dependencies/<?= $dependency->id() ?>/edit"><?= htmlspecialchars($t('common.edit')) ?></a><a class="button-secondary" href="/diagrams/components"><?= htmlspecialchars($t('dependencies.overview_diagram')) ?></a></div>
        </div>

        <?php if ($dependency->isIncomplete()): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($t('dependencies.incomplete_warning')) ?></div>
        <?php endif; ?>

        <section class="two-column">
            <div class="panel">
                <h3><?= htmlspecialchars($t('dependencies.source_and_target')) ?></h3>
                <dl>
                    <dt><?= htmlspecialchars($t('table.source')) ?></dt><dd><?= htmlspecialchars($componentName($dependency->sourceComponentId())) ?></dd>
                    <dt><?= htmlspecialchars($t('table.target')) ?></dt><dd><?= htmlspecialchars($componentName($dependency->targetComponentId())) ?></dd>
                    <dt><?= htmlspecialchars($t('form.direction')) ?></dt><dd><?= htmlspecialchars($dependency->direction()) ?></dd>
                    <dt><?= htmlspecialchars($t('form.bidirectional')) ?></dt><dd><?= $dependency->isBidirectional() ? htmlspecialchars($t('common.yes')) : htmlspecialchars($t('common.no')) ?></dd>
                </dl>
            </div>
            <div class="panel">
                <h3><?= htmlspecialchars($t('dependencies.classification')) ?></h3>
                <dl>
                    <dt><?= htmlspecialchars($t('dependencies.type_id')) ?></dt><dd><?= $dependency->dependencyTypeId() ?></dd>
                    <dt><?= htmlspecialchars($t('dependencies.protocol_id')) ?></dt><dd><?= $dependency->protocolId() ?? '—' ?></dd>
                    <dt><?= htmlspecialchars($t('table.status_id')) ?></dt><dd><?= $dependency->statusId() ?></dd>
                    <dt><?= htmlspecialchars($t('dependencies.criticality_id')) ?></dt><dd><?= $dependency->criticalityId() ?? '—' ?></dd>
                    <dt><?= htmlspecialchars($t('table.owner')) ?></dt><dd><?= htmlspecialchars($personName($dependency->ownerId())) ?></dd>
                </dl>
            </div>
        </section>

        <section class="panel">
            <h3><?= htmlspecialchars($t('dependencies.documentation')) ?></h3>
            <dl>
                <dt><?= htmlspecialchars($t('form.data_description')) ?></dt><dd><?= nl2br(htmlspecialchars((string) $dependency->dataDescription())) ?: '—' ?></dd>
                <dt><?= htmlspecialchars($t('table.frequency')) ?></dt><dd><?= htmlspecialchars((string) $dependency->frequency()) ?: '—' ?></dd>
                <dt><?= htmlspecialchars($t('dependencies.authentication')) ?></dt><dd><?= htmlspecialchars((string) $dependency->authenticationMethod()) ?: '—' ?></dd>
                <dt><?= htmlspecialchars($t('form.documentation_url')) ?></dt><dd><?= $dependency->documentationUrl() ? '<a href="' . htmlspecialchars($dependency->documentationUrl()) . '">' . htmlspecialchars($dependency->documentationUrl()) . '</a>' : '—' ?></dd>
                <dt><?= htmlspecialchars($t('form.technical_notes')) ?></dt><dd><?= nl2br(htmlspecialchars((string) $dependency->technicalNotes())) ?: '—' ?></dd>
            </dl>
        </section>
    <?php endif; ?>
</x-layout>

