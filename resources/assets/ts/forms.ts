function toggleClass(element: HTMLElement | null, className: string, enabled: boolean): void {
  if (!element) {
    return;
  }

  element.classList.toggle(className, enabled);
}

export function registerComponentFormInteractions(): void {
  const form = document.querySelector<HTMLFormElement>('[data-component-form]');
  if (!form) {
    return;
  }

  const external = form.querySelector<HTMLInputElement>('[name="is_external"]');
  const vendorField = form.querySelector<HTMLElement>('[data-field="vendor"]');
  const status = form.querySelector<HTMLSelectElement>('[name="status_id"]');
  const lifecycle = form.querySelector<HTMLTextAreaElement>('[name="lifecycle_notes"]');

  const updateExternalState = (): void => {
    toggleClass(vendorField, 'form-field-highlight', external?.checked === true);
  };

  const updateLifecycleState = (): void => {
    const selected = status?.selectedOptions.item(0)?.textContent?.trim() ?? '';
    const retired = selected === 'Retired';
    if (lifecycle) {
      lifecycle.required = retired;
    }
    toggleClass(lifecycle?.closest('.form-field') as HTMLElement | null, 'form-field-required', retired);
  };

  external?.addEventListener('change', updateExternalState);
  status?.addEventListener('change', updateLifecycleState);
  updateExternalState();
  updateLifecycleState();
}

export function registerDependencyFormInteractions(): void {
  const form = document.querySelector<HTMLFormElement>('[data-dependency-form]');
  if (!form) {
    return;
  }

  const type = form.querySelector<HTMLSelectElement>('[name="dependency_type_id"]');
  const protocol = form.querySelector<HTMLSelectElement>('[name="protocol_id"]');
  const technicalNotes = form.querySelector<HTMLTextAreaElement>('[name="technical_notes"]');

  const updateManualTransferState = (): void => {
    const selected = type?.selectedOptions.item(0)?.textContent?.trim() ?? '';
    const isManual = selected === 'Manual Transfer';
    if (protocol) {
      protocol.required = !isManual && selected.includes('API');
    }
    toggleClass(technicalNotes?.closest('.form-field') as HTMLElement | null, 'form-field-muted', isManual);
  };

  type?.addEventListener('change', updateManualTransferState);
  updateManualTransferState();
}

