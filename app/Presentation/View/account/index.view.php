<?php
/** @var array<string, mixed> $user */
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('account.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('account.description')) ?></p>
        </div>
    </div>

    <section class="panel">
        <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('account.profile')) ?></h3>
        <dl>
            <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.email')) ?></dt>
            <dd><?= htmlspecialchars((string) ($user['email'] ?? '—')) ?></dd>
            <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.person')) ?></dt>
            <dd><?= htmlspecialchars((string) ($user['person_name'] ?? '—')) ?></dd>
            <dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.roles')) ?></dt>
            <dd><?= htmlspecialchars(implode(', ', $user['roles'] ?? [])) ?></dd>
        </dl>
    </section>

    <section class="panel">
        <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('account.language_settings')) ?></h3>
        <form method="POST" action="/preferences/language">
            <?= \App\Shared\Support\Csrf::input() ?>
            <input type="hidden" name="redirect_to" value="/account">
            <div class="form-grid">
                <div class="form-field">
                    <label for="locale"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('language.label')) ?></label>
                    <select id="locale" name="locale">
                        <?php foreach (\App\Shared\Support\Translator::supportedLocales() as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $code === (string) ($user['preferred_locale'] ?? 'en') ? 'selected' : '' ?>><?= htmlspecialchars(
                                $label,
                            ) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="button-primary" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('language.save')) ?></button>
            </div>
        </form>
    </section>
</x-layout>
