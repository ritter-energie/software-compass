<?php /** @var \App\Domain\Dependency\Dependency $dependency */ ?>
<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.edit')) ?>: <?= htmlspecialchars($dependency->name()) ?></h2>

    <form method="POST" action="/dependencies/<?= $dependency->id() ?>" data-dependency-form="true">
        <?= \App\Shared\Support\Csrf::input() ?>
        <x-dependency-form
            :dependency="$dependency"
            :components="$components"
            :dependency-types="$dependencyTypes"
            :protocols="$protocols"
            :statuses="$statuses"
            :criticality-levels="$criticalityLevels"
            :people="$people"
        />

        <div class="form-actions actions">
            <button type="submit" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.save_changes')) ?></button>
            <a class="button-secondary" href="/dependencies/<?= $dependency->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.cancel')) ?></a>
        </div>
    </form>

    <form class="delete-form" method="POST" action="/dependencies/<?= $dependency->id() ?>/delete" data-confirm="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.confirm_delete')) ?>">
        <?= \App\Shared\Support\Csrf::input() ?>
        <button type="submit" class="button-danger"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dependencies.delete')) ?></button>
    </form>
</x-layout>

