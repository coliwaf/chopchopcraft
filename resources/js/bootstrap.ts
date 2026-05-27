/* import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute('content');

if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token; 
}*/

import axios from 'axios'
import { route as ziggyRoute, Config as ZiggyConfig } from 'ziggy-js'

// ─── Axios global defaults ────────────────────────────────────────────────────
// Every fetch/axios request automatically sends the CSRF token Laravel expects.
// This is required for POST/PATCH/DELETE routes protected by VerifyCsrfToken.
window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Laravel reads this header to return CSRF-validated responses
const csrfToken = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content
}

// ─── Ziggy route() global ─────────────────────────────────────────────────────
// The @routes Blade directive injects window.Ziggy (the route list JSON).
// We bind ziggyRoute() to that config so route('products.index') works anywhere.
declare global {
    interface Window {
        axios: typeof axios
        Ziggy: ZiggyConfig
        route: typeof ziggyRoute
    }
}

window.route = (
    name: string,
    params?: unknown,
    absolute?: boolean,
    config?: ZiggyConfig,
) => ziggyRoute(name, params, absolute, config ?? window.Ziggy)