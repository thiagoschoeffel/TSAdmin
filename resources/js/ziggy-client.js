import { Ziggy as ZiggyStatic } from "./ziggy.js";
import { route as ziggyRoute } from "ziggy-js";

// Export Ziggy static (the generated config). We'll compute a runtime config
// that uses the browser's origin when available to avoid cross-origin URLs.
export { ZiggyStatic as Ziggy };

// Compute runtime config when calling route: if we're in a browser, prefer the
// current origin so generated absolute URLs match the page origin. This also
// prevents CORS issues when the generated Ziggy.url differs (e.g. 127.0.0.1 vs localhost).
const runtimeConfig =
    typeof window !== "undefined" && window.location
        ? { ...ZiggyStatic, url: window.location.origin }
        : ZiggyStatic;

// Export a wrapper that uses the runtime config. Default `absolute` to false so
// Inertia uses same-origin relative paths unless an absolute URL is specifically requested.
export const route = (name, params = {}, absolute = false) =>
    ziggyRoute(name, params, absolute, runtimeConfig);
