<?php
/**
 * @var \App\Domain\Component\Component $component
 */
?>
<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.edit')) ?>: <?= htmlspecialchars($component->name()) ?></h2>

    <x-form :action="'/components/' . $component->id()" method="POST" data-component-form="true">
        <?= \App\Shared\Support\Csrf::input() ?>
        <x-component-form
            :component="$component"
            :component-types="$componentTypes"
            :statuses="$statuses"
            :criticality-levels="$criticalityLevels"
            :environments="$environments"
            :deployment-locations="$deploymentLocations"
            :people="$people"
            :available-components="$availableComponents"
        />

        <div class="form-actions">
            <button type="submit" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.save_changes')) ?></button>
            <a class="button-secondary" href="/components/<?= $component->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.cancel')) ?></a>
        </div>
    </x-form>

    <form class="delete-form" method="POST" action="/components/<?= $component->id() ?>/delete" data-confirm="<?= htmlspecialchars(\App\Shared\Support\Translator::translate(
        'components.confirm_delete',
    )) ?>">
        <?= \App\Shared\Support\Csrf::input() ?>
        <button type="submit" class="button-danger"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.delete')) ?></button>
    </form>
</x-layout>
