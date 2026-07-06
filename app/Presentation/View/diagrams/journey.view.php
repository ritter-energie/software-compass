<?php
/** @var int $journeyId */
/** @var string $mermaid */
?>
<x-layout>
    <div class="page-header"><h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.journey_title')) ?></h2><a class="button-secondary" href="/journeys/<?= $journeyId ?>"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
        'journeys.back_to_journey',
    )) ?></a></div>
    <div class="diagram-container"><pre class="mermaid"><?= htmlspecialchars($mermaid) ?></pre></div>
    <details><summary><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.mermaid_source')) ?></summary><pre id="journey-mermaid-source" class="mermaid-source"><?= htmlspecialchars(
        $mermaid,
    ) ?></pre></details>
    <button type="button" data-copy-mermaid="#journey-mermaid-source"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.copy_mermaid_source')) ?></button>
</x-layout>
