<?php
/**
 * @var \App\Domain\Governance\GovernanceReview[] $reviews
 * @var \App\Domain\Component\Component[] $components
 */
$checksDone = static fn (int|float $done): string => str_replace('{count}', (string) $done, \App\Shared\Support\Translator::translate('governance.checks_done'));
$componentName = static function (int $id) use ($components): string {
    foreach ($components as $component)
        if ($component->id() === $id)
            return $component->name();
    return 'C' . $id;
};
?>
<x-layout>
    <div class="page-header"><div><h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.reviews')) ?></h2><p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.description')) ?></p></div><a class="button-secondary" href="/components/create"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
        'governance.create_component',
    )) ?></a></div>
    <table class="data-table">
        <thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.component')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'table.checklist',
        )) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.actions')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($reviews as $review): ?>
            <?php $done = array_sum([
                $review->duplicateCheckDone(),
                $review->interfaceCheckDone(),
                $review->ownerCheckDone(),
                $review->dataCheckDone(),
                $review->deploymentCheckDone(),
            ]); ?>
            <tr><td><?= htmlspecialchars($componentName($review->componentId())) ?></td><td><span class="badge badge-warning"><?= htmlspecialchars(\App\Shared\Support\GovernanceStatusLabel::from($review->reviewStatus())) ?></span></td><td><?= htmlspecialchars($checksDone(
                $done,
            )) ?></td><td><a href="/governance/reviews/<?= $review->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.open_review')) ?></a></td></tr>
        <?php endforeach; ?>
        <?php if ($reviews === []): ?><tr><td colspan="4"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_open_governance_reviews')) ?></td></tr><?php endif; ?>
        </tbody>
    </table>
</x-layout>
