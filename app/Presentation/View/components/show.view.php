<?php
/**
 * @var \App\Domain\Component\Component|null $component
 * @var \App\Application\Component\ComponentDetailViewModel|null $detail
 * @var \App\Domain\Person\Person[] $people
 */
$personName = static function (?int $id, array $people): ?string {
    if ($id === null) {
        return null;
    }

    foreach ($people as $person) {
        if ($person->id() === $id) {
            return $person->name();
        }
    }

    return null;
};

$t = static fn (string $key, mixed ...$arguments): string => \App\Shared\Support\Translator::translate($key, ...$arguments);
?>
<x-layout>
    <?php if ($component === null): ?>
        <h2><?= htmlspecialchars($t('components.not_found')) ?></h2>
        <p><a href="/components"><?= htmlspecialchars($t('components.back_to_list')) ?></a></p>
    <?php else: ?>
        <div class="page-header">
            <h2><?= htmlspecialchars($component->name()) ?></h2>
            <div class="actions">
                <a class="button-secondary" href="/components/<?= $component->id() ?>/edit"><?= htmlspecialchars($t('common.edit')) ?></a>
                <a class="button-secondary" href="/components/<?= $component->id() ?>/diagram"><?= htmlspecialchars($t('nav.diagrams')) ?></a>
                <a class="button-secondary" href="/components/<?= $component->id() ?>/governance"><?= htmlspecialchars($t('nav.governance')) ?></a>
            </div>
        </div>

        <?php if ($detail !== null && $detail->warnings !== []): ?>
            <div class="alert alert-warning">
                <strong><?= htmlspecialchars($t('components.incomplete_information')) ?></strong>
                <ul>
                    <?php foreach ($detail->warnings as $warning): ?>
                        <li><?= htmlspecialchars($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="detail-grid">
            <div>
                <h3><?= htmlspecialchars($t('components.overview')) ?></h3>
                <dl>
                    <dt>Slug</dt><dd><?= htmlspecialchars($component->slug()) ?></dd>
                    <dt><?= htmlspecialchars($t('form.short_name')) ?></dt><dd><?= htmlspecialchars((string) $component->shortName()) ?: '—' ?></dd>
                    <dt><?= htmlspecialchars($t('form.project_name')) ?></dt><dd><?= htmlspecialchars((string) $component->projectName()) ?: '—' ?></dd>
                    <dt><?= htmlspecialchars($t('form.vendor')) ?></dt><dd><?= htmlspecialchars((string) $component->vendor()) ?: '—' ?></dd>
                    <dt><?= htmlspecialchars($t('table.external')) ?></dt><dd><?= $component->isExternal() ? htmlspecialchars($t('common.yes')) : htmlspecialchars($t('common.no')) ?></dd>
                    <dt><?= htmlspecialchars($t('form.started_on')) ?></dt><dd><?= $component->startedOn()?->format('Y-m-d') ?? '—' ?></dd>
                </dl>
            </div>

            <div>
                <h3><?= htmlspecialchars($t('components.responsibilities')) ?></h3>
                <dl>
                    <dt><?= htmlspecialchars($t('form.business_owner')) ?></dt><dd><?= htmlspecialchars($personName($component->businessOwnerId(), $people) ?? '—') ?></dd>
                    <dt><?= htmlspecialchars($t('form.technical_owner')) ?></dt><dd><?= htmlspecialchars($personName($component->technicalOwnerId(), $people) ?? '—') ?></dd>
                </dl>
            </div>
        </section>

        <section>
            <h3><?= htmlspecialchars($t('components.purpose_and_description')) ?></h3>
            <p><?= nl2br(htmlspecialchars((string) $component->purpose())) ?: '<em>' . htmlspecialchars($t('common.no_purpose_documented')) . '</em>' ?></p>
            <p><?= nl2br(htmlspecialchars((string) $component->description())) ?></p>
        </section>

        <section>
            <h3><?= htmlspecialchars($t('components.documentation')) ?></h3>
            <ul>
                <li><?= htmlspecialchars($t('form.documentation_url')) ?>: <?= $component->documentationUrl() ? '<a href="' . htmlspecialchars($component->documentationUrl()) . '">' . htmlspecialchars($component->documentationUrl()) . '</a>' : '<em>' . htmlspecialchars($t('common.none')) . '</em>' ?></li>
                <li><?= htmlspecialchars($t('form.repository_url')) ?>: <?= $component->repositoryUrl() ? '<a href="' . htmlspecialchars($component->repositoryUrl()) . '">' . htmlspecialchars($component->repositoryUrl()) . '</a>' : '<em>' . htmlspecialchars($t('common.none')) . '</em>' ?></li>
            </ul>
        </section>

        <?php if ($detail !== null): ?>
            <section>
                <h3><?= htmlspecialchars($t('components.incoming_dependencies')) ?> (<?= count($detail->incomingDependencies) ?>)</h3>
                <table class="data-table">
                    <thead><tr><th><?= htmlspecialchars($t('table.name')) ?></th><th><?= htmlspecialchars($t('components.from')) ?></th><th><?= htmlspecialchars($t('table.data')) ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($detail->incomingDependencies as $dependency): ?>
                        <tr><td><?= htmlspecialchars($dependency->name()) ?></td><td>C<?= $dependency->sourceComponentId() ?></td><td><?= htmlspecialchars((string) $dependency->dataDescription()) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if ($detail->incomingDependencies === []): ?>
                        <tr><td colspan="3"><?= htmlspecialchars($t('common.none')) ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section>
                <h3><?= htmlspecialchars($t('components.outgoing_dependencies')) ?> (<?= count($detail->outgoingDependencies) ?>)</h3>
                <table class="data-table">
                    <thead><tr><th><?= htmlspecialchars($t('table.name')) ?></th><th><?= htmlspecialchars($t('components.to')) ?></th><th><?= htmlspecialchars($t('table.data')) ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($detail->outgoingDependencies as $dependency): ?>
                        <tr><td><?= htmlspecialchars($dependency->name()) ?></td><td>C<?= $dependency->targetComponentId() ?></td><td><?= htmlspecialchars((string) $dependency->dataDescription()) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if ($detail->outgoingDependencies === []): ?>
                        <tr><td colspan="3"><?= htmlspecialchars($t('common.none')) ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <p><a href="/dependencies/create?source_component_id=<?= $component->id() ?>">+ <?= htmlspecialchars($t('components.add_dependency_from_component')) ?></a></p>
            </section>

            <section>
                <h3><?= htmlspecialchars($t('components.governance_status')) ?></h3>
                <?php if ($detail->governanceReview !== null): ?>
                    <p><?= htmlspecialchars($t('table.status')) ?>: <strong><?= htmlspecialchars(\App\Shared\Support\GovernanceStatusLabel::from($detail->governanceReview->reviewStatus())) ?></strong></p>
                <?php else: ?>
                    <p><em><?= htmlspecialchars($t('components.no_governance_review_yet')) ?></em></p>
                <?php endif; ?>
                <p><a href="/components/<?= $component->id() ?>/governance"><?= htmlspecialchars($t('components.view_governance_details')) ?></a></p>
            </section>

            <section>
                <h3><?= htmlspecialchars($t('components.local_diagram')) ?></h3>
                <pre class="mermaid"><?= htmlspecialchars($detail->mermaidDiagram) ?></pre>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</x-layout>
