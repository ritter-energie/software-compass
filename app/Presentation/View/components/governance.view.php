<?php
/**
 * @var \App\Domain\Component\Component $component
 * @var \App\Domain\Governance\GovernanceReview|null $review
 */
?>
<x-layout>
    <h2><?= htmlspecialchars($component->name()) ?> — <?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.governance')) ?></h2>
    <p><a href="/components/<?= $component->id() ?>">&larr; <?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.back_to_component')) ?></a></p>

    <?php if ($review === null): ?>
        <p><em><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.no_governance_review_yet')) ?></em></p>
    <?php else: ?>
        <form method="POST" action="/governance/reviews/<?= $review->id() ?>">
            <?= \App\Shared\Support\Csrf::input() ?>

            <p><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status')) ?>: <strong><?= htmlspecialchars(\App\Shared\Support\GovernanceStatusLabel::from($review->reviewStatus())) ?></strong></p>

            <label><input type="checkbox" name="duplicate_check_done" value="1" <?= $review->duplicateCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.duplicate_check_done')) ?></label><br>
            <label><input type="checkbox" name="interface_check_done" value="1" <?= $review->interfaceCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.interface_check_done')) ?></label><br>
            <label><input type="checkbox" name="owner_check_done" value="1" <?= $review->ownerCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.owner_check_done')) ?></label><br>
            <label><input type="checkbox" name="data_check_done" value="1" <?= $review->dataCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.data_check_done')) ?></label><br>
            <label><input type="checkbox" name="deployment_check_done" value="1" <?= $review->deploymentCheckDone() ? 'checked' : '' ?>> <?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.deployment_check_done')) ?></label><br>

            <label for="notes"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.notes')) ?></label>
            <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars((string) $review->notes()) ?></textarea>

            <div class="form-actions">
                <button type="submit" class="button-secondary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.save_checklist')) ?></button>
                <button type="submit" formaction="/governance/reviews/<?= $review->id() ?>/approve" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.approve')) ?></button>
                <button type="submit" formaction="/governance/reviews/<?= $review->id() ?>/reject" class="button-danger"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.reject')) ?></button>
            </div>
        </form>
    <?php endif; ?>
</x-layout>

