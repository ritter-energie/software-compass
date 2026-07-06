<?php

declare(strict_types=1);

use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;
use Tempest\Http\Session\Session;

use function Tempest\get;

/** @var Session $session */
$session = get(Session::class);
$success = $session->consume('success');
$error = $session->consume('error');
?>
<!doctype html>
<html lang="<?= htmlspecialchars(Translator::locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(Translator::translate('auth.login_title')) ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="auth-page">
<main class="auth-shell" aria-labelledby="login-title">
    <section class="auth-hero" aria-label="<?= htmlspecialchars(Translator::translate('auth.login_hero_label')) ?>">
        <div class="auth-brand" aria-hidden="true">SC</div>
        <p class="auth-kicker"><?= htmlspecialchars(Translator::translate('app.title')) ?></p>
        <h1 id="login-title"><?= htmlspecialchars(Translator::translate('auth.login_title')) ?></h1>
        <p><?= htmlspecialchars(Translator::translate('auth.login_description')) ?></p>
        <ul class="auth-highlights">
            <li><?= htmlspecialchars(Translator::translate('auth.highlight_components')) ?></li>
            <li><?= htmlspecialchars(Translator::translate('auth.highlight_governance')) ?></li>
            <li><?= htmlspecialchars(Translator::translate('auth.highlight_diagrams')) ?></li>
        </ul>
    </section>

    <section class="panel auth-card" aria-labelledby="login-form-title">
        <p class="auth-kicker"><?= htmlspecialchars(Translator::translate('auth.secure_access')) ?></p>
        <h2 id="login-form-title"><?= htmlspecialchars(Translator::translate('auth.login_form_title')) ?></h2>
        <p class="muted"><?= htmlspecialchars(Translator::translate('auth.login_form_description')) ?></p>

        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars((string) $error) ?></div><?php endif; ?>

        <form method="POST" action="/login">
            <?= Csrf::input() ?>
            <div class="form-grid">
                <div class="form-field form-field-wide">
                    <label for="email"><?= htmlspecialchars(Translator::translate('auth.email')) ?></label>
                    <input id="email" name="email" type="email" autocomplete="email" required autofocus>
                </div>
                <div class="form-field form-field-wide">
                    <label for="password"><?= htmlspecialchars(Translator::translate('auth.password')) ?></label>
                    <input id="password" name="password" type="password" required>
                </div>
            </div>
            <div class="actions">
                <button class="button-primary" type="submit"><?= htmlspecialchars(Translator::translate('auth.login_submit')) ?></button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
