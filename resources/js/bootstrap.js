window._ = require('lodash');
window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token
let token = document.head.querySelector('meta[name="_token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found!');
}

// CRITICAL: Enable credentials for all requests
window.axios.defaults.withCredentials = true;

// Laravel Echo & Pusher
import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: false,
    encrypted: false,

    // CRITICAL: Use axios for auth requests (includes credentials)
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                window.axios.post('/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    callback(null, response.data);
                })
                .catch(error => {
                    console.error('Broadcasting auth error:', error);
                    callback(error);
                });
            }
        };
    }
});

