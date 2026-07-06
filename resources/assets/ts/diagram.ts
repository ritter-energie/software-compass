import mermaid from 'mermaid';

let initialized = false;

export function initializeMermaid(): void {
  if (initialized) {
    return;
  }

  mermaid.initialize({
    startOnLoad: true,
    securityLevel: 'strict',
    theme: 'default',
    flowchart: {
      useMaxWidth: true,
      htmlLabels: false,
    },
  });

  initialized = true;
}

export function registerMermaidCopyButtons(): void {
  document.querySelectorAll<HTMLButtonElement>('[data-copy-mermaid]').forEach((button) => {
    button.addEventListener('click', async () => {
      const targetSelector = button.dataset.copyMermaid;
      if (!targetSelector) {
        return;
      }

      const target = document.querySelector<HTMLElement>(targetSelector);
      const text = target?.textContent?.trim();
      if (!text) {
        return;
      }

      await navigator.clipboard.writeText(text);
      button.textContent = 'Copied';
      window.setTimeout(() => {
        button.textContent = 'Copy Mermaid source';
      }, 1800);
    });
  });
}

