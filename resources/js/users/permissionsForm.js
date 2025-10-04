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
    const moduleToggles = container.querySelectorAll('[data-module-toggle]');
    const fieldsets = container.querySelectorAll('fieldset[data-permissions-resource]');
    const note = container.querySelector('[data-admin-permissions-note]');
    if (isAdmin) {
        snapshotPermissions(container);
        // Enable all modules and abilities, lock UI
        moduleToggles.forEach((toggle) => {
            toggle.checked = true;
            toggle.setAttribute('disabled', 'disabled');
        });
        boxes.forEach((box) => {
            box.checked = true;
            box.setAttribute('disabled', 'disabled');
        });
        fieldsets.forEach((fs) => fs.setAttribute('disabled', 'disabled'));
        if (note) note.classList.remove('hidden');
    } else {
        moduleToggles.forEach((toggle) => toggle.removeAttribute('disabled'));
        boxes.forEach((box) => box.removeAttribute('disabled'));
        fieldsets.forEach((fs) => fs.removeAttribute('disabled'));
        restorePermissions(container);
        if (note) note.classList.add('hidden');
    }
};

const setModuleState = (fieldset, enabled) => {
    const abilityBoxes = fieldset.querySelectorAll('[data-abilities] input[type="checkbox"][name^="permissions["]');
    abilityBoxes.forEach((box) => {
        if (enabled) {
            box.removeAttribute('disabled');
        } else {
            box.checked = false;
            box.setAttribute('disabled', 'disabled');
        }
    });
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

        // Bind module toggles
        container.querySelectorAll('fieldset[data-permissions-resource]').forEach((fieldset) => {
            const toggle = fieldset.querySelector('[data-module-toggle]');
            if (!toggle) return;
            // Initialize abilities state based on toggle
            setModuleState(fieldset, toggle.checked);
            toggle.addEventListener('change', () => setModuleState(fieldset, toggle.checked));
        });
    });
};

document.addEventListener('DOMContentLoaded', initPermissionsForm);
