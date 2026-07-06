<?php /** @var \App\Application\Dashboard\DashboardViewModel $dashboard */ ?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dashboard.title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dashboard.description')) ?></p>
        </div>
    </div>

    <section class="cards">
        <?php foreach ($dashboard->metrics as $label => $value): ?>
            <article class="card"><strong><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dashboard.metrics.' . $label)) ?></strong><span><?= (int) $value ?></span></article>
        <?php endforeach; ?>
    </section>

    <section class="two-column">
        <div class="panel">
            <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dashboard.recent_components')) ?></h3>
            <table><thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.purpose')) ?></th></tr></thead><tbody>
            <?php foreach ($dashboard->recentComponents as $component): ?>
                <tr><td><a href="/components/<?= $component['id'] ?>"><?= htmlspecialchars($component['name']) ?></a></td><td><?= htmlspecialchars((string) $component['purpose']) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>

        <div class="panel">
            <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dashboard.open_reviews')) ?></h3>
            <table><thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.component')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status')) ?></th></tr></thead><tbody>
            <?php foreach ($dashboard->openReviews as $review): ?>
                <tr><td><a href="/components/<?= $review['component_id'] ?>">C<?= (int) $review['component_id'] ?></a></td><td><?= htmlspecialchars(\App\Shared\Support\GovernanceStatusLabel::from((string) $review['review_status'])) ?></td></tr>
            <?php endforeach; ?>
            <?php if ($dashboard->openReviews === []): ?><tr><td colspan="2"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_open_reviews')) ?></td></tr><?php endif; ?>
            </tbody></table>
        </div>

        <div class="panel">
            <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dashboard.incomplete_components')) ?></h3>
            <table><thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.missing_information')) ?></th></tr></thead><tbody>
            <?php foreach ($dashboard->incompleteComponents as $component): ?>
                <tr><td><a href="/components/<?= $component['id'] ?>"><?= htmlspecialchars($component['name']) ?></a></td><td><span class="badge badge-warning"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.needs_documentation')) ?></span></td></tr>
            <?php endforeach; ?>
            <?php if ($dashboard->incompleteComponents === []): ?><tr><td colspan="2"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_incomplete_components')) ?></td></tr><?php endif; ?>
            </tbody></table>
        </div>

        <div class="panel">
            <h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('dashboard.critical_dependencies')) ?></h3>
            <table><thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.name')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.data')) ?></th></tr></thead><tbody>
            <?php foreach ($dashboard->criticalDependencies as $dependency): ?>
                <tr><td><a href="/dependencies/<?= $dependency['id'] ?>"><?= htmlspecialchars($dependency['name']) ?></a></td><td><?= htmlspecialchars((string) $dependency['data_description']) ?></td></tr>
            <?php endforeach; ?>
            <?php if ($dashboard->criticalDependencies === []): ?><tr><td colspan="2"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_critical_dependencies')) ?></td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </section>
</x-layout>
