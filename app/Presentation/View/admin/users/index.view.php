<?php
/** @var array<int, array<string, mixed>> $users */
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.description')) ?></p>
        </div>
        <a class="button-primary" href="/admin/users/create">+ <?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.create')) ?></a>
    </div>

    <table class="data-table">
        <thead>
        <tr>
            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.person')) ?></th>
            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.email')) ?></th>
            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.roles')) ?></th>
            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.locale')) ?></th>
            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status')) ?></th>
            <th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars((string) ($user['person_name'] ?? '—')) ?></td>
                <td><?= htmlspecialchars((string) ($user['email'] ?? '—')) ?></td>
                <td><?= htmlspecialchars(implode(', ', $user['roles'] ?? [])) ?></td>
                <td><?= htmlspecialchars((string) ($user['preferred_locale'] ?? 'en')) ?></td>
                <td>
                    <span class="badge <?= $user['is_active'] ? 'badge-success' : 'badge-warning' ?>">
                        <?= htmlspecialchars(
                            $user['is_active'] ? \App\Shared\Support\Translator::translate('users.active') : \App\Shared\Support\Translator::translate('users.inactive'),
                        ) ?>
                    </span>
                </td>
                <td>
                    <a class="button-secondary" href="/admin/users/<?= (int) $user['id'] ?>/edit">
                        <?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.edit')) ?>
                    </a>
                    <form method="POST" action="/admin/users/<?= (int) $user['id'] ?>/toggle-active">
                        <?= \App\Shared\Support\Csrf::input() ?>
                        <button class="button-secondary" type="submit">
                            <?= htmlspecialchars(
                                $user['is_active'] ? \App\Shared\Support\Translator::translate('users.deactivate') : \App\Shared\Support\Translator::translate('users.activate'),
                            ) ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($users === []): ?>
            <tr><td colspan="6"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('users.none')) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</x-layout>
