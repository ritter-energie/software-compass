<?php

declare(strict_types=1);

/** @var string $mermaid */
?>
<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.global_customer_journey_title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.global_customer_journey_description')) ?></p>
        </div>
        <a class="button-secondary" href="/journeys"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('journeys.back_to_list')) ?></a>
    </div>
    <div class="diagram-container"><pre class="mermaid"><?= htmlspecialchars($mermaid) ?></pre></div>
    <details><summary><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.mermaid_source')) ?></summary><pre id="global-customer-journey-mermaid-source" class="mermaid-source"><?= htmlspecialchars(
        $mermaid,
    ) ?></pre></details>
    <button type="button" data-copy-mermaid="#global-customer-journey-mermaid-source"><?= htmlspecialchars(\App\Shared\Support\Translator::translate(
        'diagrams.copy_mermaid_source',
    )) ?></button>
</x-layout>
