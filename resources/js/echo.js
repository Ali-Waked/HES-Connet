import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const token = window.Laravel?.sanctumToken ?? localStorage.getItem('sanctum_token');

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    wssPort: import.meta.env.VITE_PUSHER_PORT,
    enabledTransports: ["ws", "wss"],
    authEndpoint: '/api/broadcasting/auth',
    auth: token ? {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
        },
    } : undefined,
});
