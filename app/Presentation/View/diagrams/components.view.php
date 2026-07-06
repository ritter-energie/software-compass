<x-layout>
    <div class="page-header">
        <div>
            <h2><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.components_title')) ?></h2>
            <p class="muted"><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.description')) ?></p>
        </div>
    </div>
    <pre class="mermaid"><?= htmlspecialchars($mermaid) ?></pre>
    <details><summary><?= htmlspecialchars(\App\Shared\Support\Translator::translate('diagrams.mermaid_source')) ?></summary><pre><?= htmlspecialchars($mermaid) ?></pre></details>
</x-layout>
