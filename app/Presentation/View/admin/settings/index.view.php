<?php

use App\Application\Setup\DatabaseUpdateStatus;
use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;

/** @var DatabaseUpdateStatus $status */
/** @var string $defaultLocale */
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(Translator::translate('app_settings.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(Translator::translate('app_settings.description')) ?></p>
        </div>
    </div>

    <section class="panel stack">
        <div>
            <h3><?= htmlspecialchars(Translator::translate('app_settings.global_settings')) ?></h3>
            <p class="muted"><?= htmlspecialchars(Translator::translate('app_settings.global_settings_description')) ?></p>
        </div>
        <form method="POST" action="/admin/settings/default-locale">
            <?= Csrf::input() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label for="default_locale"><?= htmlspecialchars(Translator::translate('app_settings.default_locale')) ?></label>
                    <select id="default_locale" name="default_locale">
                        <?php foreach (Translator::supportedLocales() as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $code === $defaultLocale ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="hint"><?= htmlspecialchars(Translator::translate('app_settings.default_locale_hint')) ?></p>
                </div>
            </div>
            <div class="actions">
                <button class="button-primary" type="submit"><?= htmlspecialchars(Translator::translate('language.save')) ?></button>
            </div>
        </form>
    </section>

    <?php if ($status->hasValidationErrors()): ?>
        <section class="alert alert-danger">
            <strong><?= htmlspecialchars(Translator::translate('database_update.validation_errors')) ?></strong>
            <ul class="warning-list">
                <?php foreach ($status->validationErrors as $validationError): ?>
                    <li><?= htmlspecialchars($validationError) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section class="panel stack">
        <div>
            <h3><?= htmlspecialchars(Translator::translate('database_update.title')) ?></h3>
            <p class="muted"><?= htmlspecialchars(Translator::translate('database_update.description')) ?></p>
            <?php if ($status->migrationTableMissing): ?>
                <p class="alert alert-warning"><?= htmlspecialchars(Translator::translate('database_update.migration_table_missing')) ?></p>
            <?php elseif ($status->hasPendingMigrations()): ?>
                <p class="alert alert-warning"><?= htmlspecialchars(Translator::translate('database_update.pending_notice')) ?></p>
            <?php elseif (! $status->hasValidationErrors()): ?>
                <p class="alert alert-success"><?= htmlspecialchars(Translator::translate('database_update.up_to_date')) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($status->hasPendingMigrations()): ?>
            <div>
                <h3><?= htmlspecialchars(Translator::translate('database_update.pending_migrations')) ?></h3>
                <ul>
                    <?php foreach ($status->pendingMigrations as $migrationName): ?>
                        <li><code><?= htmlspecialchars($migrationName) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/settings/database-update" class="actions">
            <?= Csrf::input() ?>
            <button class="button-primary" type="submit" <?= $status->hasPendingMigrations() && ! $status->hasValidationErrors() ? '' : 'disabled' ?>>
                <?= htmlspecialchars(Translator::translate('database_update.run_update')) ?>
            </button>
            <a class="button-secondary" href="/dashboard"><?= htmlspecialchars(Translator::translate('common.cancel')) ?></a>
        </form>
    </section>
</x-layout>
