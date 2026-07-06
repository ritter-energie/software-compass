<?php

use App\Infrastructure\Persistence\AppSettingsRepository;
use App\Infrastructure\Security\CurrentUser;
use App\Shared\Support\Csrf;
use App\Shared\Support\UserInitials;
use Tempest\Http\Session\Session;

use function Tempest\get;

/** @var Session $session */
$session = get(Session::class);
$success = $session->consume('success');
$error = $session->consume('error');
$locale = \App\Shared\Support\Translator::locale();
$networkName = get(AppSettingsRepository::class)->get('network_name');
$currentUserDisplayName = CurrentUser::displayName();
$currentUserInitials = UserInitials::fromName($currentUserDisplayName);
?>
<!doctype html>
<html lang="<?= htmlspecialchars($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(\App\Shared\Support\Translator::translate('app.title')) ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<header class="topbar">
    <h1>
        <?= htmlspecialchars(\App\Shared\Support\Translator::translate('app.title')) ?>
        <?php if ($networkName !== null && $networkName !== ''): ?>
            <small> - <?= htmlspecialchars($networkName) ?></small>
        <?php endif; ?>
    </h1>
    <nav>
        <a href="/dashboard"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.dashboard')) ?></a>
        <a href="/components"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.components')) ?></a>
        <a href="/dependencies"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.interfaces')) ?></a>
        <a href="/journeys"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.journeys')) ?></a>
        <a href="/governance"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.governance')) ?></a>
        <a href="/diagrams/components"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.diagrams')) ?></a>
        <a href="/master-data"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.master_data')) ?></a>
        <?php if (CurrentUser::hasRole('admin')): ?>
            <a href="/admin/users"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.users')) ?></a>
        <?php endif; ?>
    </nav>
    <?php if (CurrentUser::userId() !== null): ?>
        <div class="user-menu" data-user-menu>
            <button
                type="button"
                class="user-menu__trigger"
                aria-haspopup="menu"
                aria-expanded="false"
                aria-controls="user-menu-dropdown"
                aria-label="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.account_menu')) ?>"
                title="<?= htmlspecialchars($currentUserDisplayName ?? \App\Shared\Support\Translator::translate('nav.account_menu')) ?>"
                data-user-menu-trigger
            ><?= htmlspecialchars($currentUserInitials) ?></button>
            <div id="user-menu-dropdown" class="user-menu__dropdown" role="menu" hidden data-user-menu-dropdown>
                <a class="user-menu__item" role="menuitem" href="/account"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.account')) ?></a>
                <form method="POST" action="/logout" role="none">
                    <?= Csrf::input() ?>
                    <button type="submit" class="user-menu__item user-menu__item-button" role="menuitem">
                        <?= htmlspecialchars(\App\Shared\Support\Translator::translate('auth.logout')) ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</header>
<main class="container">
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars((string) $error) ?></div><?php endif; ?>
    <x-slot />
</main>
<footer class="footer"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('footer.tagline')) ?></footer>
<script type="module" src="/assets/app.js"></script>
</body>
</html>
