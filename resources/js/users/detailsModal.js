import Modal from '../modules/modal';

const loadingTemplate = `
    <div class="space-y-6" data-modal-state="loading" aria-hidden="true">
        <div class="space-y-3">
            <div class="skeleton h-6 w-48 rounded-md"></div>
            <div class="skeleton h-4 w-64 rounded-md"></div>
        </div>
        <div class="space-y-4">
            <div class="skeleton h-5 w-40 rounded-md"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <div class="skeleton h-4 w-24 rounded-md"></div>
                    <div class="skeleton h-4 w-32 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-24 rounded-md"></div>
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                </div>
            </div>
        </div>
        <div class="space-y-4">
            <div class="skeleton h-5 w-32 rounded-md"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                    <div class="skeleton h-4 w-36 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="skeleton h-4 w-28 rounded-md"></div>
                    <div class="skeleton h-4 w-32 rounded-md"></div>
                </div>
            </div>
        </div>
    </div>
    <span class="sr-only">Carregando detalhes do usuário...</span>
`;

const errorTemplate = `
    <div class="modal__empty" data-modal-state="error">
        <p class="text-sm text-rose-600">Não foi possível carregar os detalhes do usuário.</p>
        <button type="button" class="btn-secondary" data-user-details-retry>Tentar novamente</button>
    </div>
`;

class UserDetailsModal extends Modal {
    constructor(modal) {
        super(modal);

        this.body = modal.querySelector('[data-modal-body]');
        this.placeholderLabelId = modal.getAttribute('aria-labelledby');
        this.controller = null;
        this.lastUrl = null;
    }

    load(url) {
        if (!url || !this.body) {
            return;
        }

        this.abort();
        this.lastUrl = url;

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
                    throw new Error('Failed to load user details');
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

        const retryButton = this.body.querySelector('[data-user-details-retry]');
        if (retryButton) {
            retryButton.addEventListener('click', () => {
                if (this.lastUrl) {
                    this.load(this.lastUrl);
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
        const heading = this.body?.querySelector('#user-details-modal-title');
        if (heading?.id) {
            this.modal.setAttribute('aria-labelledby', heading.id);
        }
    }
}

const SELECTOR = '[data-user-details-trigger]';

const bootstrapUserDetailsModal = () => {
    const modalElement = document.querySelector('[data-modal="user-details"]');
    if (!modalElement) {
        return;
    }

    const modal = new UserDetailsModal(modalElement);

    const bindTrigger = (element) => {
        if (!element || element.dataset.userDetailsBound === 'true') {
            return;
        }

        element.dataset.userDetailsBound = 'true';

        element.addEventListener('click', (event) => {
            event.preventDefault();

            const url = element.dataset.userDetailsUrl;
            if (!url) {
                return;
            }

            modal.load(url);
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

document.addEventListener('DOMContentLoaded', bootstrapUserDetailsModal);

