<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.new')) ?></h2>

    <form method="POST" action="/dependencies" data-dependency-form="true">
        <?= \App\Shared\Support\Csrf::input() ?>
        <x-dependency-form
            :dependency="null"
            :components="$components"
            :dependency-types="$dependencyTypes"
            :protocols="$protocols"
            :statuses="$statuses"
            :criticality-levels="$criticalityLevels"
            :people="$people"
        />

        <div class="form-actions actions">
            <button type="submit" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.create')) ?></button>
            <a class="button-secondary" href="/dependencies"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.cancel')) ?></a>
        </div>
    </form>
</x-layout>
