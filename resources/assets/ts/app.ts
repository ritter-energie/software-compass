import '../sass/app.scss';
import { registerDeleteConfirmations } from './confirm';
import { initializeMermaid, registerMermaidCopyButtons } from './diagram';
import { registerComponentFormInteractions, registerDependencyFormInteractions } from './forms';
import { registerUserMenu } from './navigation';

function boot(): void {
  initializeMermaid();
  registerMermaidCopyButtons();
  registerDeleteConfirmations();
  registerComponentFormInteractions();
  registerDependencyFormInteractions();
  registerUserMenu();
}

document.addEventListener('DOMContentLoaded', boot);

