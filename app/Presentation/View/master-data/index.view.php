<?php
/**
 * @var \App\Domain\ReferenceData\ReferenceDataType[] $types
 * @var array<string, \App\Domain\ReferenceData\ReferenceDataEntry[]> $entriesByType
 * @var bool $canManage
 */
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.description')) ?></p>
        </div>
    </div>

    <section class="grid two-columns">
        <?php foreach ($types as $type): ?>
            <?php $entries = $entriesByType[$type->value] ?? []; ?>
            <article class="panel">
                <div class="page-header compact-header">
                    <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate($type->titleKey())) ?></h3>
                    <?php if ($canManage): ?>
                        <a class="button-secondary" href="/master-data/<?= htmlspecialchars($type->value) ?>/create">+ <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                            'master_data.create_entry',
                        )) ?></a>
                    <?php endif; ?>
                </div>
                <?php if ($entries === []): ?>
                    <p><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.no_entries')) ?></p>
                <?php else: ?>
                    <table class="data-table compact-table">
                        <thead>
                        <tr>
                            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th>
                            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.details')) ?></th>
                            <?php if ($canManage): ?>
                                <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.actions')) ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars(\App\Shared\Support\ReferenceDataValueFormatter::format($entry->name)) ?></td>
                                <td>
                                    <?php $details = []; ?>
                                    <?php foreach ($type->fields() as $field): ?>
                                        <?php if ($field === \App\Domain\ReferenceData\ReferenceDataField::NAME) {
                                            continue;
                                        } ?>
                                        <?php $details[] =
                                            \App\Shared\Support\Translator::translate($field->labelKey())
                                            . ': '
                                            . \App\Shared\Support\ReferenceDataValueFormatter::format($entry->value($field)); ?>
                                    <?php endforeach; ?>
                                    <?= htmlspecialchars($details === [] ? '—' : implode(', ', $details)) ?>
                                </td>
                                <?php if ($canManage): ?>
                                    <td>
                                        <a class="button-secondary" href="/master-data/<?= htmlspecialchars($type->value) ?>/<?= (int) $entry->id ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                                            'common.edit',
                                        )) ?></a>
                                        <form method="POST" action="/master-data/<?= htmlspecialchars($type->value) ?>/<?= (int) $entry->id ?>/delete" data-confirm="<?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                                            'master_data.confirm_delete',
                                        )) ?>">
                                            <?= \App\Shared\Support\Csrf::input() ?>
                                            <button class="button-danger" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.delete')) ?></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
</x-layout>
