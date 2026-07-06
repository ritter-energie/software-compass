<?php
/**
 * @var \App\Domain\Journey\Journey $journey
 * @var \App\Domain\Journey\JourneyStep[] $steps
 * @var array<int, \App\Domain\Journey\JourneyStepComponent[]> $assignments
 * @var \App\Domain\Person\Person[] $people
 * @var \App\Domain\Component\Component[] $components
 * @var string[] $roles
 * @var string $mermaid
 */
$personName = static function (?int $id) use ($people): string { if ($id === null) return '—'; foreach ($people as $person) if ($person->id() === $id) return $person->name(); return '—'; };
$componentName = static function (int $id) use ($components): string { foreach ($components as $component) if ($component->id() === $id) return $component->name(); return 'C' . $id; };
?>
<x-layout>
    <div class="page-header"><h2><?= htmlspecialchars($journey->name()) ?></h2><div class="actions"><a class="button-secondary" href="/journeys/<?= $journey->id() ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.edit')) ?></a><a class="button-secondary" href="/diagrams/journeys/<?= $journey->id() ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('nav.diagrams')) ?></a></div></div>
    <section class="panel"><h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.master_data')) ?></h3><dl><dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.owner')) ?></dt><dd><?= htmlspecialchars($personName($journey->ownerId())) ?></dd><dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.status_id')) ?></dt><dd><?= $journey->statusId() ?></dd><dt><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.description')) ?></dt><dd><?= nl2br(htmlspecialchars((string) $journey->description())) ?: '—' ?></dd></dl></section>

    <section class="panel">
        <div class="section-header"><h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.steps')) ?></h3><a class="button-secondary" href="/journeys/<?= $journey->id() ?>/steps/create">+ <?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.add_step')) ?></a></div>
        <?php foreach ($steps as $step): ?>
            <article class="panel">
                <div class="section-header"><h4><?= $step->sortOrder() ?>. <?= htmlspecialchars($step->name()) ?></h4><div class="actions"><a href="/journey-steps/<?= $step->id() ?>/edit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.edit_step')) ?></a><form method="POST" action="/journey-steps/<?= $step->id() ?>/delete" data-confirm="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.confirm_delete_step')) ?>"><?= \App\Shared\Support\Csrf::input() ?><button class="button-danger" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.delete')) ?></button></form></div></div>
                <p><?= nl2br(htmlspecialchars((string) $step->description())) ?></p>
                <h5><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.assigned_components')) ?></h5>
                <table class="data-table"><thead><tr><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.component')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.role')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.notes')) ?></th><th><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.actions')) ?></th></tr></thead><tbody>
                <?php foreach ($assignments[(int) $step->id()] ?? [] as $assignment): ?>
                    <tr><td><?= htmlspecialchars($componentName($assignment->componentId())) ?></td><td><?= htmlspecialchars($assignment->roleInStep()) ?></td><td><?= htmlspecialchars((string) $assignment->notes()) ?></td><td><form method="POST" action="/journey-step-components/<?= $assignment->id() ?>/delete" data-confirm="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.confirm_remove_assignment')) ?>"><?= \App\Shared\Support\Csrf::input() ?><input type="hidden" name="journey_id" value="<?= $journey->id() ?>"><button class="button-danger" type="submit"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.remove_assignment')) ?></button></form></td></tr>
                <?php endforeach; ?>
                <?php if (($assignments[(int) $step->id()] ?? []) === []): ?><tr><td colspan="4"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_components_assigned')) ?></td></tr><?php endif; ?>
                </tbody></table>
                <form class="filter-bar" method="POST" action="/journey-steps/<?= $step->id() ?>/components">
                    <?= \App\Shared\Support\Csrf::input() ?>
                    <select name="component_id" required><option value=""><?= htmlspecialchars(\App\Shared\Support\Translator::translate('table.component')) ?></option><?php foreach ($components as $component): ?><option value="<?= $component->id() ?>"><?= htmlspecialchars($component->name()) ?></option><?php endforeach; ?></select>
                    <select name="role_in_step" required><?php foreach ($roles as $role): ?><option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option><?php endforeach; ?></select>
                    <input type="text" name="notes" placeholder="<?= htmlspecialchars(\App\Shared\Support\Translator::translate('governance.notes')) ?>">
                    <button type="submit" class="button-secondary"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.assign_component')) ?></button>
                </form>
            </article>
        <?php endforeach; ?>
        <?php if ($steps === []): ?><p><?= htmlspecialchars(\App\Shared\Support\Translator::translate('common.no_steps_yet')) ?></p><?php endif; ?>
    </section>

    <section class="panel"><h3><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.diagram')) ?></h3><pre class="mermaid"><?= htmlspecialchars($mermaid) ?></pre><details><summary><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.mermaid_source')) ?></summary><pre><?= htmlspecialchars($mermaid) ?></pre></details></section>
</x-layout>

