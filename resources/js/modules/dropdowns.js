const initDropdowns = () => {
    const dropdowns = [];

    const registerDropdown = (toggle, menu) => {
        if (!toggle || !menu) {
            return;
        }

        const parent = menu.parentElement;
        const placeholder = document.createComment('dropdown-placeholder');
        parent?.insertBefore(placeholder, menu);

        const align = menu.dataset.dropdownAlign ?? 'start';
        const offset = Number(menu.dataset.dropdownOffset ?? 8);
        const margin = Number.isFinite(offset) ? offset : 8;

        const state = {
            toggle,
            menu,
            placeholder,
            align,
            margin,
            isOpen: false,
            position() {
                const rect = toggle.getBoundingClientRect();
                const width = Math.max(menu.offsetWidth || 0, toggle.offsetWidth || 0);
                const guard = 16;

                let left;

                switch (align) {
                    case 'end':
                        left = rect.right - width;
                        break;
                    case 'center':
                        left = rect.left + rect.width / 2 - width / 2;
                        break;
                    default:
                        left = rect.left;
                }

                left = Math.min(
                    Math.max(guard, left),
                    Math.max(guard, window.innerWidth - width - guard)
                );

                menu.style.left = `${left}px`;
                menu.style.top = `${rect.bottom + margin}px`;
            },
            open() {
                if (state.isOpen) {
                    return;
                }

                dropdowns
                    .filter((item) => item !== state)
                    .forEach((item) => item.close());

                menu.classList.remove('hidden');
                document.body.appendChild(menu);
                menu.style.position = 'fixed';
                menu.style.zIndex = '1000';
                menu.style.right = 'auto';

                const width = Math.max(menu.offsetWidth || 0, toggle.offsetWidth || 0, 170);
                menu.style.minWidth = `${width}px`;

                state.position();

                requestAnimationFrame(() => {
                    menu.classList.add('is-open');
                });

                state.isOpen = true;
            },
            close() {
                if (!state.isOpen) {
                    return;
                }

                state.isOpen = false;
                menu.classList.remove('is-open');

                setTimeout(() => {
                    if (state.isOpen) {
                        return;
                    }

                    menu.classList.add('hidden');
                    ['position', 'zIndex', 'top', 'left', 'right', 'minWidth'].forEach((prop) => {
                        menu.style[prop] = '';
                    });

                    state.placeholder?.parentNode?.insertBefore(menu, state.placeholder);
                }, 160);
            },
        };

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (state.isOpen) {
                state.close();
            } else {
                state.open();
            }
        });

        dropdowns.push(state);
    };

    const closeAll = () => dropdowns.forEach((item) => item.close());

    document.addEventListener('click', (event) => {
        dropdowns.forEach((state) => {
            if (!state.isOpen) {
                return;
            }

            const clickedToggle = state.toggle.contains(event.target);
            const clickedMenu = state.menu.contains(event.target);

            if (!clickedToggle && !clickedMenu) {
                state.close();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    document.addEventListener('scroll', closeAll, true);

    window.addEventListener('resize', () => {
        dropdowns.forEach((state) => {
            if (state.isOpen) {
                state.position();
            }
        });
    });

    registerDropdown(
        document.querySelector('[data-user-menu-toggle]'),
        document.querySelector('[data-user-menu]')
    );

    document.querySelectorAll('[data-menu-toggle]').forEach((toggle) => {
        const menuId = toggle.getAttribute('data-menu-toggle');
        const menu = document.querySelector(`[data-menu-panel="${menuId}"]`);
        registerDropdown(toggle, menu);
    });
};

document.addEventListener('DOMContentLoaded', initDropdowns);
