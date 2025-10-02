import Modal from '../modules/modal';

const loadingTemplate = `
    <div class="space-y-6" data-modal-state="loading" aria-hidden="true">
        <div class="space-y-3">
            <div class="skeleton h-6 w-48 rounded-md"></div>
            <div class="skeleton h-4 w-64 rounded-md"></div>
        </div>
        <div class="space-y-4">
            <div class="skeleton h-5 w-44 rounded-md"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                    <div class="skeleton h-4 w-full rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-24 rounded-md"></div>
                    <div class="skeleton h-4 w-3/4 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                    <div class="skeleton h-4 w-2/3 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-32 rounded-md"></div>
                    <div class="skeleton h-4 w-1/2 rounded-md"></div>
                </div>
            </div>
        </div>
        <div class="space-y-4">
            <div class="skeleton h-5 w-36 rounded-md"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                    <div class="skeleton h-4 w-full rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                    <div class="skeleton h-4 w-2/3 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                    <div class="skeleton h-4 w-3/4 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-24 rounded-md"></div>
                    <div class="skeleton h-4 w-1/2 rounded-md"></div>
                </div>
            </div>
        </div>
        <div class="space-y-4">
            <div class="skeleton h-5 w-32 rounded-md"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <div class="skeleton h-4 w-36 rounded-md"></div>
                    <div class="skeleton h-4 w-3/4 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-36 rounded-md"></div>
                    <div class="skeleton h-4 w-2/3 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-32 rounded-md"></div>
                    <div class="skeleton h-4 w-1/2 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-40 rounded-md"></div>
                    <div class="skeleton h-4 w-5/6 rounded-md"></div>
                </div>
            </div>
        </div>
    </div>
    <span class="sr-only">Carregando detalhes do cliente...</span>
`;

const errorTemplate = `
    <div class="modal__empty" data-modal-state="error">
        <p class="text-sm text-rose-600">Não foi possível carregar os detalhes do cliente.</p>
        <button type="button" class="btn-secondary" data-client-details-retry>Tentar novamente</button>
    </div>
`;

class ClientDetailsModal extends Modal {
    constructor(modal) {
        super(modal);

        this.body = modal.querySelector('[data-modal-body]');
        this.placeholderLabelId = modal.getAttribute('aria-labelledby');
        this.controller = null;
        this.lastUrl = null;
        this.lastTrigger = null;
    }

    load(url, trigger = null) {
        if (!url || !this.body) {
            return;
        }

        this.abort();
        this.lastUrl = url;
        this.lastTrigger = trigger;

        this.setLoading();
        this.open();

        this.controller = new AbortController();

        fetch(url, {
            headers: {
                Accept: 'application/json',
            },
            signal: this.controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Failed to load client details');
                }

                return response.json();
            })
            .then((data) => {
                if (!data?.html) {
                    throw new Error('Invalid payload');
                }

                this.setBody(data.html);
            })
            .catch((error) => {
                if (error.name === 'AbortError') {
                    return;
                }

                this.setError();
            })
            .finally(() => {
                this.controller = null;
            });
    }

    abort() {
        if (this.controller) {
            this.controller.abort();
            this.controller = null;
        }
    }

    close() {
        this.abort();
        return super.close();
    }

    setLoading() {
        this.resetAriaLabel();

        if (this.body) {
            this.body.innerHTML = loadingTemplate;
        }

        this.modal?.setAttribute('aria-busy', 'true');
    }

    setError() {
        this.resetAriaLabel();

        if (!this.body) {
            return;
        }

        this.body.innerHTML = errorTemplate;
        this.modal?.removeAttribute('aria-busy');

        const retryButton = this.body.querySelector('[data-client-details-retry]');
        if (retryButton) {
            retryButton.addEventListener('click', () => {
                if (this.lastUrl) {
                    this.load(this.lastUrl, this.lastTrigger);
                }
            }, { once: true });
        }
    }

    setBody(html) {
        if (!this.body) {
            return;
        }

        this.body.innerHTML = html;
        this.syncAriaFromContent();
        this.modal?.removeAttribute('aria-busy');
    }

    resetAriaLabel() {
        if (this.placeholderLabelId) {
            this.modal.setAttribute('aria-labelledby', this.placeholderLabelId);
        }
    }

    syncAriaFromContent() {
        const heading = this.body?.querySelector('#client-details-modal-title');
        if (heading?.id) {
            this.modal.setAttribute('aria-labelledby', heading.id);
        }
    }
}

const closeDropdown = (trigger) => {
    const menu = trigger.closest('[data-menu-panel]');
    if (!menu) {
        return;
    }

    const panelId = menu.dataset.menuPanel;
    if (panelId) {
        const toggle = document.querySelector(`[data-menu-toggle="${panelId}"]`);
        if (toggle) {
            toggle.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
            return;
        }
    }

    menu.classList.remove('is-open');
    menu.classList.add('hidden');
    ['position', 'zIndex', 'top', 'left', 'right', 'minWidth'].forEach((prop) => {
        menu.style[prop] = '';
    });
    menu.removeAttribute('hidden');
};

const SELECTOR = '[data-client-details-trigger]';

const bootstrapClientDetailsModal = () => {
    const modalElement = document.querySelector('[data-modal="client-details"]');
    if (!modalElement) {
        return;
    }

    const modal = new ClientDetailsModal(modalElement);

    const bindTrigger = (element) => {
        if (!element || element.dataset.clientDetailsBound === 'true') {
            return;
        }

        element.dataset.clientDetailsBound = 'true';

        element.addEventListener('click', (event) => {
            event.preventDefault();

            const url = element.dataset.clientDetailsUrl;
            if (!url) {
                return;
            }

            closeDropdown(element);
            modal.load(url, element);
        });
    };

    document.querySelectorAll(SELECTOR).forEach(bindTrigger);

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) {
                    return;
                }

                if (node.matches?.(SELECTOR)) {
                    bindTrigger(node);
                }

                node.querySelectorAll?.(SELECTOR).forEach(bindTrigger);
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
};

document.addEventListener('DOMContentLoaded', bootstrapClientDetailsModal);
