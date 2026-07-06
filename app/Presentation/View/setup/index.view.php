<?php

declare(strict_types=1);

use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(Translator::translate('setup.title')) ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<main class="container">
    <section class="panel" style="max-width: 50rem; margin: 2rem auto;">
        <h1><?= htmlspecialchars(Translator::translate('setup.title')) ?></h1>
        <p class="muted"><?= htmlspecialchars(Translator::translate('setup.description')) ?></p>

        <form method="POST" action="/setup">
            <?= Csrf::input() ?>
            <div class="form-grid">
                <div class="form-field form-field-wide">
                    <label for="network_name"><?= htmlspecialchars(Translator::translate('setup.network_name')) ?></label>
                    <input id="network_name" name="network_name" required>
                </div>
                <div class="form-field">
                    <label for="admin_name"><?= htmlspecialchars(Translator::translate('setup.admin_name')) ?></label>
                    <input id="admin_name" name="admin_name" required>
                </div>
                <div class="form-field">
                    <label for="admin_email"><?= htmlspecialchars(Translator::translate('setup.admin_email')) ?></label>
                    <input id="admin_email" name="admin_email" type="email" autocomplete="email" required>
                </div>
                <div class="form-field">
                    <label for="default_locale"><?= htmlspecialchars(Translator::translate('setup.default_locale')) ?></label>
                    <select id="default_locale" name="default_locale">
                        <?php foreach (Translator::supportedLocales() as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="password"><?= htmlspecialchars(Translator::translate('setup.password')) ?></label>
                    <input id="password" name="password" type="password" required minlength="8">
                </div>
                <div class="form-field">
                    <label for="password_confirmation"><?= htmlspecialchars(Translator::translate('setup.password_confirmation')) ?></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8">
                </div>
            </div>
            <div class="actions">
                <button class="button-primary" type="submit"><?= htmlspecialchars(Translator::translate('setup.submit')) ?></button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
