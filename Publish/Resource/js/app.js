// Import vue  s
import './extra';

// Fontawesome
import '@fortawesome/fontawesome-free/js/all.js';

// Components
import './components/inputs';

// Feather icons
import feather from 'feather-icons'

(function() {
    "use strict";

    // Feather Icons
    feather.replace({
        'stroke-width': 1.5
    })
    window.feather = feather
})();
