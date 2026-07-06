<?php
/**
 * @var \App\Domain\Component\Component|null $component
 * @var \App\Application\Component\ComponentDetailViewModel|null $detail
 * @var string $businessOwnerName
 * @var string $technicalOwnerName
 */
?>
<x-layout>
    <?php if ($component === null): ?>
        <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.not_found')) ?></h2>
        <p><a href="/components"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.back_to_list')) ?></a></p>
    <?php else: ?>
        <div class="page-header">
            <h2><?= htmlspecialchars($component->name()) ?></h2>
            <div class="actions">
                <a class="button-secondary" href="/components/<?= $component->id() ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.edit')) ?></a>
                <a class="button-secondary" href="/components/<?= $component->id() ?>/diagram"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.diagrams')) ?></a>
                <a class="button-secondary" href="/components/<?= $component->id() ?>/governance"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.governance')) ?></a>
            </div>
        </div>

        <?php if ($detail !== null && $detail->warnings !== []): ?>
            <div class="alert alert-warning">
                <strong><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.incomplete_information')) ?></strong>
                <ul>
                    <?php foreach ($detail->warnings as $warning): ?>
                        <li><?= htmlspecialchars($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="detail-grid">
            <div>
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.overview')) ?></h3>
                <dl>
                    <dt>Slug</dt><dd><?= htmlspecialchars($component->slug()) ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.short_name')) ?></dt><dd><?= htmlspecialchars((string) $component->shortName()) ?: '—' ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.project_name')) ?></dt><dd><?= htmlspecialchars((string) $component->projectName())
                        ?: '—' ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.vendor')) ?></dt><dd><?= htmlspecialchars((string) $component->vendor()) ?: '—' ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.external')) ?></dt><dd><?= $component->isExternal()
                        ? htmlspecialchars(\App\Shared\Support\Translator::translate('common.yes'))
                        : htmlspecialchars(\App\Shared\Support\Translator::translate('common.no')) ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.started_on')) ?></dt><dd><?= $component->startedOn()?->format('Y-m-d') ?? '—' ?></dd>
                </dl>
            </div>

            <div>
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.responsibilities')) ?></h3>
                <dl>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.business_owner')) ?></dt><dd><?= htmlspecialchars($businessOwnerName) ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.technical_owner')) ?></dt><dd><?= htmlspecialchars($technicalOwnerName) ?></dd>
                </dl>
            </div>
        </section>

        <section>
            <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.purpose_and_description')) ?></h3>
            <p><?= nl2br(htmlspecialchars((string) $component->purpose()))
                ?: '<em>' . htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_purpose_documented')) . '</em>' ?></p>
            <p><?= nl2br(htmlspecialchars((string) $component->description())) ?></p>
        </section>

        <section>
            <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.documentation')) ?></h3>
            <ul>
                <li><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.documentation_url')) ?>: <?= $component->documentationUrl()
                    ? '<a href="' . htmlspecialchars($component->documentationUrl()) . '">' . htmlspecialchars($component->documentationUrl()) . '</a>'
                    : '<em>' . htmlspecialchars(\App\Shared\Support\Translator::translate('common.none')) . '</em>' ?></li>
                <li><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.repository_url')) ?>: <?= $component->repositoryUrl()
                    ? '<a href="' . htmlspecialchars($component->repositoryUrl()) . '">' . htmlspecialchars($component->repositoryUrl()) . '</a>'
                    : '<em>' . htmlspecialchars(\App\Shared\Support\Translator::translate('common.none')) . '</em>' ?></li>
            </ul>
        </section>

        <?php if ($detail !== null): ?>
            <section>
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.inheritance')) ?></h3>
                <div class="detail-grid">
                    <div>
                        <h4><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.parent_components')) ?> (<?= count($detail->parentComponents) ?>)</h4>
                        <?php if ($detail->parentComponents === []): ?>
                            <p><em><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.no_parent_components')) ?></em></p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($detail->parentComponents as $parentComponent): ?>
                                    <li><a href="/components/<?= $parentComponent->id() ?>"><?= htmlspecialchars($parentComponent->name()) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h4><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.child_components')) ?> (<?= count($detail->childComponents) ?>)</h4>
                        <?php if ($detail->childComponents === []): ?>
                            <p><em><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.no_child_components')) ?></em></p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($detail->childComponents as $childComponent): ?>
                                    <li><a href="/components/<?= $childComponent->id() ?>"><?= htmlspecialchars($childComponent->name()) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section>
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.incoming_dependencies')) ?> (<?= count($detail->incomingDependencies) ?>)</h3>
                <table class="data-table">
                    <thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                        'components.from',
                    )) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.data')) ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($detail->incomingDependencies as $dependency): ?>
                        <tr><td><?= htmlspecialchars($dependency->name()) ?></td><td>C<?= $dependency->sourceComponentId() ?></td><td><?= htmlspecialchars((string) $dependency->dataDescription()) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if ($detail->incomingDependencies === []): ?>
                        <tr><td colspan="3"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none')) ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section>
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.outgoing_dependencies')) ?> (<?= count($detail->outgoingDependencies) ?>)</h3>
                <table class="data-table">
                    <thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                        'components.to',
                    )) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.data')) ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($detail->outgoingDependencies as $dependency): ?>
                        <tr><td><?= htmlspecialchars($dependency->name()) ?></td><td>C<?= $dependency->targetComponentId() ?></td><td><?= htmlspecialchars((string) $dependency->dataDescription()) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if ($detail->outgoingDependencies === []): ?>
                        <tr><td colspan="3"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.none')) ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <p><a href="/dependencies/create?source_component_id=<?= $component->id() ?>">+ <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                    'components.add_dependency_from_component',
                )) ?></a></p>
            </section>

            <section>
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.governance_status')) ?></h3>
                <?php if ($detail->governanceReview !== null): ?>
                    <p><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status')) ?>: <strong><?= htmlspecialchars(\App\Shared\Support\GovernanceStatusLabel::from($detail->governanceReview->reviewStatus())) ?></strong></p>
                <?php else: ?>
                    <p><em><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.no_governance_review_yet')) ?></em></p>
                <?php endif; ?>
                <p><a href="/components/<?= $component->id() ?>/governance"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.view_governance_details')) ?></a></p>
            </section>

            <section>
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.local_diagram')) ?></h3>
                <pre class="mermaid"><?= htmlspecialchars($detail->mermaidDiagram) ?></pre>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</x-layout>
