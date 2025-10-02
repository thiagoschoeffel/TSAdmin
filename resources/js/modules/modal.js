const FOCUSABLE_SELECTORS = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(', ');

export default class Modal {
    constructor(modal) {
        this.modal = modal;
        this.backdrop = modal?.querySelector('[data-modal-backdrop]') ?? null;
        this.closeButtons = modal ? Array.from(modal.querySelectorAll('[data-modal-close]')) : [];
        this.previousFocus = null;

        this.handleKeydown = this.handleKeydown.bind(this);
        this.handleFocusTrap = this.handleFocusTrap.bind(this);

        this.closeButtons.forEach((button) => {
            button.addEventListener('click', () => this.close());
        });

        this.backdrop?.addEventListener('click', () => this.close());
    }

    open() {
        if (!this.modal || this.modal.classList.contains('is-open')) {
            return false;
        }

        this.previousFocus = document.activeElement;

        this.modal.removeAttribute('hidden');
        this.modal.classList.remove('hidden');
        this.modal.setAttribute('aria-hidden', 'false');

        requestAnimationFrame(() => {
            this.modal.classList.add('is-open');
        });

        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', this.handleKeydown, true);

        this.focusFirstElement();
        return true;
    }

    close() {
        if (!this.modal?.classList.contains('is-open')) {
            return false;
        }

        this.modal.setAttribute('aria-hidden', 'true');
        this.modal.classList.remove('is-open');

        document.body.style.removeProperty('overflow');
        document.removeEventListener('keydown', this.handleKeydown, true);

        window.setTimeout(() => {
            if (this.modal.classList.contains('is-open')) {
                return;
            }

            this.modal.classList.add('hidden');
            this.modal.setAttribute('hidden', 'true');
        }, 200);

        if (this.previousFocus && typeof this.previousFocus.focus === 'function') {
            this.previousFocus.focus({ preventScroll: true });
        }

        this.previousFocus = null;
        return true;
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
            last.focus({ preventScroll: true });
            return;
        }

        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus({ preventScroll: true });
        }
    }

    focusFirstElement() {
        const focusable = this.getFocusableElements();
        if (focusable.length === 0) {
            return;
        }

        const preferred = focusable.find((element) => element.dataset.modalAutofocus === 'true' || element.dataset.confirmAutoFocus === 'true');
        (preferred ?? focusable[0]).focus({ preventScroll: true });
    }

    getFocusableElements() {
        if (!this.modal) {
            return [];
        }

        return Array.from(this.modal.querySelectorAll(FOCUSABLE_SELECTORS)).filter((element) => {
            if (element.hasAttribute('disabled') || element.getAttribute('aria-hidden') === 'true') {
                return false;
            }

            if (element.offsetParent === null && getComputedStyle(element).position !== 'fixed') {
                return false;
            }

            return true;
        });
    }
}
