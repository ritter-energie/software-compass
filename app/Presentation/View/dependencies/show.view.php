<?php
/**
 * @var \App\Presentation\ViewModel\DependencyDetailViewModel|null $detail
 */
?>
<x-layout>
    <?php if ($detail === null): ?>
        <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.not_found')) ?></h2>
        <p><a href="/dependencies"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.back_to_list')) ?></a></p>
    <?php else: ?>
        <?php $dependency = $detail->dependency; ?>
        <div class="page-header">
            <h2><?= htmlspecialchars($dependency->name()) ?></h2>
            <div class="actions"><a class="button-secondary" href="/dependencies/<?= $dependency->id() ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'common.edit',
            )) ?></a><a class="button-secondary" href="/diagrams/components"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'dependencies.overview_diagram',
            )) ?></a></div>
        </div>

        <?php if ($dependency->isIncomplete()): ?>
            <div class="alert alert-warning"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.incomplete_warning')) ?></div>
        <?php endif; ?>

        <section class="two-column">
            <div class="panel">
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.source_and_target')) ?></h3>
                <dl>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.source')) ?></dt><dd><?= htmlspecialchars($detail->sourceComponentName) ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.target')) ?></dt><dd><?= htmlspecialchars($detail->targetComponentName) ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.direction')) ?></dt><dd><?= htmlspecialchars($dependency->direction()) ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.bidirectional')) ?></dt><dd><?= $dependency->isBidirectional()
                        ? htmlspecialchars(\App\Shared\Support\Translator::translate('common.yes'))
                        : htmlspecialchars(\App\Shared\Support\Translator::translate('common.no')) ?></dd>
                </dl>
            </div>
            <div class="panel">
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.classification')) ?></h3>
                <dl>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.type_id')) ?></dt><dd><?= $dependency->dependencyTypeId() ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.protocol_id')) ?></dt><dd><?= $dependency->protocolId() ?? '—' ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status_id')) ?></dt><dd><?= $dependency->statusId() ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.criticality_id')) ?></dt><dd><?= $dependency->criticalityId() ?? '—' ?></dd>
                    <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.owner')) ?></dt><dd><?= htmlspecialchars($detail->ownerName) ?></dd>
                </dl>
            </div>
        </section>

        <section class="panel">
            <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.documentation')) ?></h3>
            <dl>
                <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.data_description')) ?></dt><dd><?= nl2br(htmlspecialchars((string) $dependency->dataDescription()))
                    ?: '—' ?></dd>
                <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.frequency')) ?></dt><dd><?= htmlspecialchars((string) $dependency->frequency()) ?: '—' ?></dd>
                <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.authentication')) ?></dt><dd><?= htmlspecialchars((string) $dependency->authenticationMethod())
                    ?: '—' ?></dd>
                <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.documentation_url')) ?></dt><dd><?= $dependency->documentationUrl()
                    ? '<a href="' . htmlspecialchars($dependency->documentationUrl()) . '">' . htmlspecialchars($dependency->documentationUrl()) . '</a>'
                    : '—' ?></dd>
                <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.technical_notes')) ?></dt><dd><?= nl2br(htmlspecialchars((string) $dependency->technicalNotes()))
                    ?: '—' ?></dd>
            </dl>
        </section>
    <?php endif; ?>
</x-layout>
