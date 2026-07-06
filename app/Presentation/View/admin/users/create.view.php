<?php
/** @var string[] $roles */
/** @var string $defaultLocale */
?>
<x-layout>
    <div class="page-header">
        <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.create')) ?></h2>
        <a class="button-secondary" href="/admin/users"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.back_to_list')) ?></a>
    </div>

    <form method="POST" action="/admin/users">
        <?= \App\Shared\Support\Csrf::input() ?>
        <div class="form-grid">
            <div class="form-field">
                <label for="name"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.person')) ?></label>
                <input id="name" name="name" required>
            </div>
            <div class="form-field">
                <label for="email"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.email')) ?></label>
                <input id="email" name="email" type="email" autocomplete="email" required>
            </div>
            <div class="form-field">
                <label for="role"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.roles')) ?></label>
                <select id="role" name="role" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="preferred_locale"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.locale')) ?></label>
                <select id="preferred_locale" name="preferred_locale">
                    <?php foreach (\App\Shared\Support\Translator::supportedLocales() as $code => $label): ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= $code === $defaultLocale ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="password"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('setup.password')) ?></label>
                <input id="password" name="password" type="password" required minlength="8">
            </div>
            <div class="form-field">
                <label for="password_confirmation"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('setup.password_confirmation')) ?></label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8">
            </div>
        </div>
        <div class="actions">
            <button class="button-primary" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.create')) ?></button>
            <a class="button-secondary" href="/admin/users"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.cancel')) ?></a>
        </div>
    </form>
</x-layout>
