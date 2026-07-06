<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.new')) ?></h2>
    <form method="POST" action="/journeys">
        <?= \App\Shared\Support\Csrf::input() ?>
        <x-journey-form :journey="null" :statuses="$statuses" :people="$people" />
        <div class="form-actions actions"><button class="button-primary" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.create')) ?></button><a class="button-secondary" href="/journeys"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
            'common.cancel',
        )) ?></a></div>
    </form>
</x-layout>
