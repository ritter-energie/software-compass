import mermaid from 'mermaid';
import svgPanZoom from 'svg-pan-zoom';

let initialized = false;

function initializePanZoom(svgId: string): void {
  const svg = document.getElementById(svgId);
  if (!(svg instanceof SVGSVGElement)) {
    return;
  }

  svgPanZoom(svg, {
    panEnabled: true,
    zoomEnabled: true,
    controlIconsEnabled: true,
    mouseWheelZoomEnabled: true,
    dblClickZoomEnabled: true,
    fit: true,
    center: true,
    minZoom: 0.1,
    maxZoom: 10,
  });
}

export function initializeMermaid(): void {
  if (initialized) {
    return;
  }

  mermaid.initialize({
    startOnLoad: false,
    securityLevel: 'loose',
    theme: 'base',
    flowchart: {
      useMaxWidth: true,
      htmlLabels: false,
    },
  });

  initialized = true;
  void mermaid.run({
    querySelector: '.mermaid',
    postRenderCallback: initializePanZoom,
  }).catch((error: unknown) => {
    console.error('Failed to render Mermaid diagrams.', error);
  });
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

