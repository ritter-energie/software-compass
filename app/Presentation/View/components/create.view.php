<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.new')) ?></h2>

    <x-form action="/components" method="POST" data-component-form="true">
        <?= \App\Shared\Support\Csrf::input() ?>
        <x-component-form
            :component="null"
            :component-types="$componentTypes"
            :statuses="$statuses"
            :criticality-levels="$criticalityLevels"
            :environments="$environments"
            :deployment-locations="$deploymentLocations"
            :people="$people"
        />

        <div class="form-actions">
            <button type="submit" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.create')) ?></button>
            <a class="button-secondary" href="/components"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.cancel')) ?></a>
        </div>
    </x-form>
</x-layout>

