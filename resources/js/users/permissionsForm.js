const snapshotPermissions = (container) => {
    const data = {};
    container.querySelectorAll('input[type="checkbox"][name^="permissions["]').forEach((box) => {
        const match = box.name.match(/^permissions\[(.+?)\]\[(.+?)\]$/);
        if (!match) return;
        const [, resource, ability] = match;
        data[resource] = data[resource] || {};
        data[resource][ability] = box.checked;
    });
    container.dataset.prevPermissions = JSON.stringify(data);
};

const restorePermissions = (container) => {
    try {
        const stored = container.dataset.prevPermissions ? JSON.parse(container.dataset.prevPermissions) : null;
        if (!stored) return;
        container.querySelectorAll('input[type="checkbox"][name^="permissions["]').forEach((box) => {
            const match = box.name.match(/^permissions\[(.+?)\]\[(.+?)\]$/);
            if (!match) return;
            const [, resource, ability] = match;
            const val = !!(stored[resource] && stored[resource][ability]);
            box.checked = val;
        });
    } catch (_) {
        // ignore parse errors
    }
};

const setAdminState = (container, isAdmin) => {
    const boxes = container.querySelectorAll('input[type="checkbox"][name^="permissions["]');
    const note = container.querySelector('[data-admin-permissions-note]');
    if (isAdmin) {
        snapshotPermissions(container);
        boxes.forEach((box) => {
            box.checked = true;
            box.setAttribute('disabled', 'disabled');
        });
        if (note) note.classList.remove('hidden');
    } else {
        boxes.forEach((box) => box.removeAttribute('disabled'));
        restorePermissions(container);
        if (note) note.classList.add('hidden');
    }
};

const initPermissionsForm = () => {
    document.querySelectorAll('[data-permissions-container]').forEach((container) => {
        const roleSelect = container.closest('form')?.querySelector('[data-role-select]');
        if (!roleSelect) return;

        const sync = () => setAdminState(container, roleSelect.value === 'admin');
        // Initial state sync on load
        sync();
        // Toggle on change
        roleSelect.addEventListener('change', sync);
    });
};

document.addEventListener('DOMContentLoaded', initPermissionsForm);
