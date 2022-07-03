import _ from 'lodash';
window._ = _;


/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

 import axios from 'axios';
 window.axios = axios;

 window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

 /**
  * Echo exposes an expressive API for subscribing to channels and listening
  * for events that are broadcast by Laravel. Echo and event broadcasting
  * allows your team to easily build robust real-time web applications.
  */

 // import Echo from 'laravel-echo';

 // import Pusher from 'pusher-js';
 // window.Pusher = Pusher;

 // window.Echo = new Echo({
 //     broadcaster: 'pusher',
 //     key: import.meta.env.VITE_PUSHER_APP_KEY,
 //     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
 //     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
 //     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
 //     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
 //     enabledTransports: ['ws', 'wss'],
 // });

// ES6 Modules or TypeScript
import Swal from 'sweetalert2'

window.Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});


import Toastify from 'toastify-js'
// Toast js
window.Toastify = Toastify;
