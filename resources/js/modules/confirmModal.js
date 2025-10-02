import Modal from './modal';

const VARIANT_CLASSES = {
    danger: 'btn-danger',
    primary: 'btn-primary',
    secondary: 'btn-secondary',
};

class ConfirmationModal extends Modal {
    constructor(modal) {
        super(modal);

        this.title = modal.querySelector('[data-confirm-title]');
        this.message = modal.querySelector('[data-confirm-message]');
        this.confirmButton = modal.querySelector('[data-confirm-confirm]');
        this.cancelButton = modal.querySelector('[data-confirm-cancel]');
        this.activeConfig = null;

        this.confirmButton?.addEventListener('click', () => this.confirm());
        this.cancelButton?.addEventListener('click', () => this.close());
    }

    open(config = {}) {
        if (!this.modal) {
            return;
        }

        this.activeConfig = config;
        if (this.title) {
            this.title.textContent = config.title ?? 'Confirmar ação';
        }
        if (this.message) {
            this.message.textContent = config.message ?? 'Tem certeza que deseja continuar?';
        }
        if (this.confirmButton) {
            this.confirmButton.textContent = config.confirmText ?? 'Confirmar';
        }
        if (this.cancelButton) {
            this.cancelButton.textContent = config.cancelText ?? 'Cancelar';
        }

        this.setVariant(config.confirmVariant);
        super.open();
    }

    close() {
        const closed = super.close();
        if (!closed) {
            return;
        }

        this.activeConfig = null;
    }

    confirm() {
        const callback = this.activeConfig?.onConfirm;
        super.close();
        if (typeof callback === 'function') {
            callback();
        }

        this.activeConfig = null;
    }

    setVariant(variant) {
        if (!this.confirmButton) {
            return;
        }

        const targetClass = VARIANT_CLASSES[variant] ?? VARIANT_CLASSES.danger;
        Object.values(VARIANT_CLASSES).forEach((className) => {
            this.confirmButton.classList.remove(className);
        });
        this.confirmButton.classList.add(targetClass);
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
