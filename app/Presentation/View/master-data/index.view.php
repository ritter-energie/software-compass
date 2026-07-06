<?php
/**
 * @var array<string, array<int, array<string, mixed>>> $groups
 */
$formatValue = static function (mixed $value): string {
    if ($value === null || $value === '') {
        return '—';
    }

    if (is_bool($value)) {
        return $value ? \App\Shared\Support\Translator::translate('common.yes') : \App\Shared\Support\Translator::translate('common.no');
    }

    return (string) $value;
};
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.description')) ?></p>
        </div>
    </div>

    <section class="grid two-columns">
        <?php foreach ($groups as $titleKey => $rows): ?>
            <article class="panel">
                <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate($titleKey)) ?></h3>
                <?php if ($rows === []): ?>
                    <p><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.no_entries')) ?></p>
                <?php else: ?>
                    <table class="data-table compact-table">
                        <thead>
                        <tr>
                            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th>
                            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.description')) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($formatValue($row['name'] ?? null)) ?></td>
                                <td>
                                    <?php if (array_key_exists('description', $row)): ?>
                                        <?= htmlspecialchars($formatValue($row['description'])) ?>
                                    <?php elseif (array_key_exists('location_type', $row)): ?>
                                        <?= htmlspecialchars($formatValue($row['location_type'])) ?>
                                    <?php elseif (array_key_exists('contains_personal_data', $row) || array_key_exists('contains_sensitive_data', $row)): ?>
                                        <?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.personal_data')) ?>:
                                        <?= htmlspecialchars($formatValue((bool) ($row['contains_personal_data'] ?? false))) ?>,
                                        <?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.sensitive_data')) ?>:
                                        <?= htmlspecialchars($formatValue((bool) ($row['contains_sensitive_data'] ?? false))) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
</x-layout>
