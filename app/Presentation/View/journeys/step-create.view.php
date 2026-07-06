<?php /** @var int $journeyId */ ?>
<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.new_step')) ?></h2>
    <form method="POST" action="/journeys/<?= $journeyId ?>/steps">
        <?= \App\Shared\Support\Csrf::input() ?>
        <div class="form-grid">
            <div class="form-field"><label for="name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.name_required')) ?></label><input id="name" name="name" required></div>
            <div class="form-field"><label for="sort_order"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.sort_order_required')) ?></label><input type="number" id="sort_order" name="sort_order" required value="0"></div>
            <div class="form-field form-field-wide"><label for="description"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('form.description')) ?></label><textarea id="description" name="description" rows="3"></textarea></div>
        </div>
        <div class="actions"><button type="submit" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.add_step')) ?></button><a class="button-secondary" href="/journeys/<?= $journeyId ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'common.cancel',
        )) ?></a></div>
    </form>
</x-layout>
