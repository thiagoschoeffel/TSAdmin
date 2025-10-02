const VARIANT_CLASSES = {
    danger: 'btn-danger',
    primary: 'btn-primary',
    secondary: 'btn-secondary',
};

class ConfirmationModal {
    constructor(modal) {
        this.modal = modal;
        this.backdrop = modal.querySelector('[data-confirm-backdrop]');
        this.title = modal.querySelector('[data-confirm-title]');
        this.message = modal.querySelector('[data-confirm-message]');
        this.confirmButton = modal.querySelector('[data-confirm-confirm]');
        this.cancelButton = modal.querySelector('[data-confirm-cancel]');
        this.activeConfig = null;
        this.previousFocus = null;

        this.handleKeydown = this.handleKeydown.bind(this);
        this.handleFocusTrap = this.handleFocusTrap.bind(this);

        this.confirmButton?.addEventListener('click', () => this.confirm());
        this.cancelButton?.addEventListener('click', () => this.close());
        this.backdrop?.addEventListener('click', () => this.close());
    }

    open(config = {}) {
        if (!this.modal) {
            return;
        }

        this.activeConfig = config;
        this.previousFocus = document.activeElement;

        this.title.textContent = config.title ?? 'Confirmar ação';
        this.message.textContent = config.message ?? 'Tem certeza que deseja continuar?';
        this.confirmButton.textContent = config.confirmText ?? 'Confirmar';
        this.cancelButton.textContent = config.cancelText ?? 'Cancelar';

        this.setVariant(config.confirmVariant);

        this.modal.removeAttribute('hidden');
        this.modal.classList.remove('hidden');
        this.modal.setAttribute('aria-hidden', 'false');

        requestAnimationFrame(() => {
            this.modal.classList.add('is-open');
        });
        document.body.style.overflow = 'hidden';

        document.addEventListener('keydown', this.handleKeydown, true);
        this.focusFirstElement();
    }

    close() {
        if (!this.modal?.classList.contains('is-open')) {
            return;
        }

        this.modal.setAttribute('aria-hidden', 'true');
        this.modal.classList.remove('is-open');
        document.body.style.removeProperty('overflow');

        window.setTimeout(() => {
            if (this.modal.classList.contains('is-open')) {
                return;
            }

            this.modal.classList.add('hidden');
            this.modal.setAttribute('hidden', 'true');
        }, 200);

        document.removeEventListener('keydown', this.handleKeydown, true);

        if (this.previousFocus && typeof this.previousFocus.focus === 'function') {
            this.previousFocus.focus({ preventScroll: true });
        }

        this.activeConfig = null;
    }

    confirm() {
        const callback = this.activeConfig?.onConfirm;
        this.close();
        if (typeof callback === 'function') {
            callback();
        }
    }

    setVariant(variant) {
        const targetClass = VARIANT_CLASSES[variant] ?? VARIANT_CLASSES.danger;
        Object.values(VARIANT_CLASSES).forEach((className) => {
            this.confirmButton.classList.remove(className);
        });
        this.confirmButton.classList.add(targetClass);
    }

    handleKeydown(event) {
        if (event.key === 'Escape') {
            event.preventDefault();
            this.close();
            return;
        }

        if (event.key === 'Tab') {
            this.handleFocusTrap(event);
        }
    }

    focusFirstElement() {
        const focusable = this.getFocusableElements();
        const preferred = focusable.find((element) => element.dataset.confirmAutoFocus === 'true');
        (preferred ?? focusable[0])?.focus({ preventScroll: true });
    }

    handleFocusTrap(event) {
        const focusable = this.getFocusableElements();
        if (focusable.length === 0) {
            event.preventDefault();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
            return;
        }

        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    getFocusableElements() {
        const selector = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled]):not([type="hidden"])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])',
        ].join(',');

        return Array.from(this.modal.querySelectorAll(selector)).filter(
            (element) => element.offsetParent !== null
        );
    }
}

const datasetToConfig = (element) => ({
    title: element.dataset.confirmTitle,
    message: element.dataset.confirmMessage,
    confirmText: element.dataset.confirmConfirmText ?? element.dataset.confirmButton,
    cancelText: element.dataset.confirmCancelText,
    confirmVariant: element.dataset.confirmVariant,
});

const bootstrapConfirmationModals = () => {
    const modal = document.querySelector('[data-confirm-modal]');
    if (!modal) {
        return;
    }

    const controller = new ConfirmationModal(modal);

    const bindElement = (element) => {
        if (!element || element.dataset.confirmBound === 'true') {
            return;
        }

        element.dataset.confirmBound = 'true';

        if (element.tagName === 'FORM') {
            element.addEventListener('submit', (event) => {
                if (element.dataset.confirmAccepted === 'true') {
                    element.dataset.confirmAccepted = '';
                    return;
                }

                event.preventDefault();

                controller.open({
                    ...datasetToConfig(element),
                    onConfirm: () => {
                        element.dataset.confirmAccepted = 'true';
                        element.submit();
                    },
                });
            });

            return;
        }

        element.addEventListener('click', (event) => {
            const href = element.getAttribute('href') ?? element.dataset.confirmHref;

            if (href) {
                event.preventDefault();
            }

            controller.open({
                ...datasetToConfig(element),
                onConfirm: () => {
                    if (href) {
                        window.location.assign(href);
                    } else if (typeof element.dataset.confirmDispatch !== 'undefined') {
                        element.dispatchEvent(new CustomEvent('confirm:accepted', { bubbles: true }));
                    }
                },
            });
        });
    };

    document.querySelectorAll('[data-confirm]').forEach(bindElement);

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) {
                    return;
                }

                if (node.dataset?.confirm !== undefined) {
                    bindElement(node);
                }

                node.querySelectorAll?.('[data-confirm]').forEach(bindElement);
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
};

document.addEventListener('DOMContentLoaded', bootstrapConfirmationModals);
