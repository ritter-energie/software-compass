<?php
/**
 * @var \App\Domain\Component\Component $component
 * @var string $mermaid
 */
?>
<x-layout>
    <h2><?= htmlspecialchars($component->name()) ?> — <?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.neighborhood_diagram')) ?></h2>
    <p><a href="/components/<?= $component->id() ?>">&larr; <?= htmlspecialchars(\App\Shared\Support\Translator::translate('components.back_to_component')) ?></a></p>
    <pre class="mermaid"><?= htmlspecialchars($mermaid) ?></pre>
    <details>
        <summary><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.mermaid_source')) ?></summary>
        <pre><?= htmlspecialchars($mermaid) ?></pre>
    </details>
</x-layout>

