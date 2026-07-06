export function registerUserMenu(): void {
  document.querySelectorAll<HTMLElement>('[data-user-menu]').forEach((menu) => {
    const trigger = menu.querySelector<HTMLButtonElement>('[data-user-menu-trigger]');
    const dropdown = menu.querySelector<HTMLElement>('[data-user-menu-dropdown]');

    if (trigger === null || dropdown === null) {
      return;
    }

    const close = (): void => {
      dropdown.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
    };

    const toggle = (): void => {
      const shouldOpen = dropdown.hidden;
      dropdown.hidden = !shouldOpen;
      trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    };

    trigger.addEventListener('click', (event) => {
      event.stopPropagation();
      toggle();
    });

    dropdown.addEventListener('click', (event) => {
      event.stopPropagation();
    });

    document.addEventListener('click', (event) => {
      if (event.target instanceof Node && !menu.contains(event.target)) {
        close();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !dropdown.hidden) {
        close();
        trigger.focus();
      }
    });
  });
}

