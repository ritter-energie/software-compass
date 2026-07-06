<?php
/**
 * @var \App\Domain\ReferenceData\ReferenceDataType $type
 * @var \App\Domain\ReferenceData\ReferenceDataEntry $entry
 */
?>
<x-layout>
    <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('master_data.edit_entry')) ?>: <?= htmlspecialchars($entry->name) ?></h2>

    <x-form :action="'/master-data/' . $type->value . '/' . (int) $entry->id" method="POST">
        <?= \App\Shared\Support\Csrf::input() ?>
        <x-master-data-form :type="$type" :entry="$entry" />

        <div class="form-actions">
            <button type="submit" class="button-primary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.save_changes')) ?></button>
            <a class="button-secondary" href="/master-data"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.cancel')) ?></a>
        </div>
    </x-form>
</x-layout>


