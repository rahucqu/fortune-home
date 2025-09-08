import axios from 'axios';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

declare global {
    interface Window {
        axios: typeof axios;
    }
}

window.axios = axios;

// Set CSRF token for all requests
const token = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
} else {
    console.error('CSRF token not found');
}

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Configure base URL
axios.defaults.baseURL = window.location.origin;

// Add response interceptor for error handling
axios.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        console.error('Axios error:', error);

        // Handle 419 (CSRF token mismatch)
        if (error.response?.status === 419) {
            console.error('CSRF token mismatch - refreshing page');
            window.location.reload();
        }

        // Handle 401 (Unauthorized)
        if (error.response?.status === 401) {
            console.error('Unauthorized - redirecting to login');
            window.location.href = '/login';
        }

        return Promise.reject(error);
    },
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';

// (window as any).Pusher = Pusher;

// (window as any).Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
// });
