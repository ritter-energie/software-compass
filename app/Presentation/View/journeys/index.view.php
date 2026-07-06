<?php /** @var \App\Presentation\ViewModel\JourneyListItemViewModel[] $journeys */ ?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.description')) ?></p>
        </div>
        <a class="button-primary" href="/journeys/create">+ <?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.new')) ?></a>
    </div>
    <table class="data-table">
        <thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'table.owner',
        )) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status_id')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'table.sort',
        )) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.actions')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($journeys as $journey): ?>
            <tr><td><a href="/journeys/<?= $journey->id ?>"><?= htmlspecialchars($journey->name) ?></a></td><td><?= htmlspecialchars($journey->ownerName) ?></td><td><?= $journey->statusId ?></td><td><?= $journey->sortOrder ?></td><td class="actions"><a href="/journeys/<?= $journey->id ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'common.view',
            )) ?></a><a href="/journeys/<?= $journey->id ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.edit')) ?></a></td></tr>
        <?php endforeach; ?>
        <?php if ($journeys === []): ?><tr><td colspan="5"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_journeys_yet')) ?></td></tr><?php endif; ?>
        </tbody>
    </table>
</x-layout>
