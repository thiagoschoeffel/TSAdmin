import { hasOpenModals } from "./modalStack";
let prevBodyPadding = null;
let prevHtmlPadding = null;
const LOCK_CLASS = "modal-scroll-locked";

function ensureLockStyle() {
    if (document.getElementById("modal-scroll-lock-style")) return;
    const style = document.createElement("style");
    style.id = "modal-scroll-lock-style";
    style.innerHTML = `.${LOCK_CLASS} { overflow: hidden !important; }`;
    document.head.appendChild(style);
}

export function lockScrollIfNeeded() {
    ensureLockStyle();

    // If already locked, do nothing
    if (
        document.documentElement.classList.contains(LOCK_CLASS) ||
        document.body.classList.contains(LOCK_CLASS)
    ) {
        return;
    }

    const scrollbarWidth =
        window.innerWidth - document.documentElement.clientWidth;

    // Save previous paddings only once so we can restore the original values later
    if (prevBodyPadding === null)
        prevBodyPadding = document.body.style.paddingRight;
    if (prevHtmlPadding === null)
        prevHtmlPadding = document.documentElement.style.paddingRight;

    if (scrollbarWidth > 0) {
        const bodyCurrent =
            parseFloat(getComputedStyle(document.body).paddingRight) || 0;
        document.body.style.paddingRight = `${bodyCurrent + scrollbarWidth}px`;
        const htmlCurrent =
            parseFloat(
                getComputedStyle(document.documentElement).paddingRight
            ) || 0;
        document.documentElement.style.paddingRight = `${
            htmlCurrent + scrollbarWidth
        }px`;
    }

    document.documentElement.classList.add(LOCK_CLASS);
    document.body.classList.add(LOCK_CLASS);
}

export function unlockScrollIfNeeded() {
    // Only unlock if there are no open modals
    if (hasOpenModals()) return;

    document.documentElement.classList.remove(LOCK_CLASS);
    document.body.classList.remove(LOCK_CLASS);

    if (prevBodyPadding !== null) {
        document.body.style.paddingRight = prevBodyPadding;
        prevBodyPadding = null;
    }
    if (prevHtmlPadding !== null) {
        document.documentElement.style.paddingRight = prevHtmlPadding;
        prevHtmlPadding = null;
    }
}
