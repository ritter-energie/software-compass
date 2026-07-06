export function registerDeleteConfirmations(): void {
  document.querySelectorAll<HTMLFormElement>('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const message = form.dataset.confirm ?? 'Are you sure?';

      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });
}

