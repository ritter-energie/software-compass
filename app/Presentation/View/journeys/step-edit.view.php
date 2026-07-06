<?php /** @var \App\Domain\Journey\JourneyStep $step */ ?>
<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.edit_step')) ?>: <?= htmlspecialchars($step->name()) ?></h2>
    <form method="POST" action="/journey-steps/<?= $step->id() ?>">
        <?= \App\Shared\Support\Csrf::input() ?>
        <div class="form-grid">
            <div class="form-field"><label for="name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.name_required')) ?></label><input id="name" name="name" required value="<?= htmlspecialchars($step->name()) ?>"></div>
            <div class="form-field"><label for="sort_order"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.sort_order_required')) ?></label><input type="number" id="sort_order" name="sort_order" required value="<?= $step->sortOrder() ?>"></div>
            <div class="form-field form-field-wide"><label for="description"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.description')) ?></label><textarea id="description" name="description" rows="3"><?= htmlspecialchars((string) $step->description()) ?></textarea></div>
        </div>
        <div class="actions"><button type="submit" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.save_step')) ?></button><a class="button-secondary" href="/journeys/<?= $step->journeyId() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.cancel')) ?></a></div>
    </form>
</x-layout>

