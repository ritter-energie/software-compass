<?php
/**
 * @var \App\Domain\Governance\GovernanceReview $review
 * @var \App\Domain\Component\Component|null $component
 * @var \App\Domain\Component\Component[] $similarComponents
 */
?>
<x-layout>
    <div class="page-header"><h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.review')) ?></h2><a class="button-secondary" href="/governance"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
        'common.back_to_reviews',
    )) ?></a></div>

    <?php if ($component !== null): ?>
        <section class="panel"><h3><?= htmlspecialchars($component->name()) ?></h3><p><?= nl2br(htmlspecialchars((string) $component->purpose())) ?: htmlspecialchars(\App\Shared\Support\Translator::translate(
            'common.no_purpose_documented',
        )) ?></p><p><a href="/components/<?= $component->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.view_component')) ?></a></p></section>
    <?php endif; ?>

    <?php if ($similarComponents !== []): ?>
        <section class="alert alert-warning"><strong><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.potential_duplicates')) ?></strong><ul><?php foreach ($similarComponents as $similar): ?><?php if (
            $component === null
            || $similar->id() !== $component->id()
        ): ?><li><a href="/components/<?= $similar->id() ?>"><?= htmlspecialchars($similar->name()) ?></a> — <?= htmlspecialchars((string) $similar->purpose()) ?></li><?php endif; ?><?php endforeach; ?></ul></section>
    <?php endif; ?>

    <form method="POST" action="/governance/reviews/<?= $review->id() ?>">
        <?= \App\Shared\Support\Csrf::input() ?>
        <p><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status')) ?>: <strong><?= htmlspecialchars(\App\Shared\Support\GovernanceStatusLabel::from($review->reviewStatus())) ?></strong></p>
        <div class="stack">
            <label><input type="checkbox" name="duplicate_check_done" value="1" <?= $review->duplicateCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'governance.duplicate_check_done',
            )) ?></label>
            <label><input type="checkbox" name="interface_check_done" value="1" <?= $review->interfaceCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'governance.interface_check_done',
            )) ?></label>
            <label><input type="checkbox" name="owner_check_done" value="1" <?= $review->ownerCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'governance.owner_check_done',
            )) ?></label>
            <label><input type="checkbox" name="data_check_done" value="1" <?= $review->dataCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'governance.data_check_done',
            )) ?></label>
            <label><input type="checkbox" name="deployment_check_done" value="1" <?= $review->deploymentCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate(
                'governance.deployment_check_done',
            )) ?></label>
            <div class="form-field"><label for="notes"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.notes')) ?></label><textarea id="notes" name="notes" rows="4"><?= htmlspecialchars((string) $review->notes()) ?></textarea></div>
        </div>
        <div class="form-actions actions"><button class="button-secondary" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.save_checklist')) ?></button><button class="button-primary" type="submit" formaction="/governance/reviews/<?= $review->id() ?>/approve"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'governance.approve',
        )) ?></button><button class="button-danger" type="submit" formaction="/governance/reviews/<?= $review->id() ?>/reject"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'governance.reject',
        )) ?></button></div>
    </form>
</x-layout>
