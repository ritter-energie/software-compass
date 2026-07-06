<?php /** @var \App\Domain\Journey\Journey $journey */ ?>
<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.edit')) ?>: <?= htmlspecialchars($journey->name()) ?></h2>
    <form method="POST" action="/journeys/<?= $journey->id() ?>">
        <?= \App\Shared\Support\Csrf::input() ?>
        <x-journey-form :journey="$journey" :statuses="$statuses" :people="$people" />
        <div class="form-actions actions"><button class="button-primary" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.save_changes')) ?></button><a class="button-secondary" href="/journeys/<?= $journey->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'common.cancel',
        )) ?></a></div>
    </form>
    <form method="POST" action="/journeys/<?= $journey->id() ?>/delete" data-confirm="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.confirm_delete')) ?>">
        <?= \App\Shared\Support\Csrf::input() ?>
        <button type="submit" class="button-danger"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.delete')) ?></button>
    </form>
</x-layout>
