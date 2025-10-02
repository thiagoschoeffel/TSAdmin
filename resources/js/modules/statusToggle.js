const updateStatusLabel = (container) => {
    const toggle = container.querySelector('[data-status-toggle]');
    const label = container.querySelector('[data-status-label]');

    if (!toggle || !label) {
        return;
    }

    const active = label.dataset.statusActive ?? 'Ativo';
    const inactive = label.dataset.statusInactive ?? 'Inativo';

    if (toggle.checked) {
        label.classList.remove('inactive');
        label.textContent = active;
    } else {
        label.classList.add('inactive');
        label.textContent = inactive;
    }
};

const initStatusControls = () => {
    document.querySelectorAll('[data-status-control]').forEach((container) => {
        const toggle = container.querySelector('[data-status-toggle]');

        if (!toggle) {
            return;
        }

        updateStatusLabel(container);

        toggle.addEventListener('change', () => updateStatusLabel(container));
    });
};

document.addEventListener('DOMContentLoaded', initStatusControls);
