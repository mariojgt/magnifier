// Load vue js
import { createApp, h } from "vue";

import tabs from "./vue_components/Tabs";
import Tab from "./vue_components/Tab";
import sidebar from "./vue_components/side-bar.vue";
import editassistant from "./vue_components/edit-assistant.vue";
import mediacontent from "./vue_components/media-content.vue";
import addfolder from "./vue_components/add-folder.vue";
import media from "./vue_components/media.vue";
import icon from "./vue_components/icon";
import breadcrumb from "./vue_components/breadcrumb";
import editassistantmedia from "./vue_components/edit-assistant-media";
// image edit
import imageedit from "./vue_components/media_edit/image-edit";

const el = document.getElementById("app");

const app = createApp({});

// Make the swwet alert avaliable to use inside vue js
app.mixin({
    methods: {
        unityToast: function (message) {
            Toastify({
                text:
                    ` <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon w-5 h-5 mx-2">
                    <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>` + message,
                duration: 2000,
                close: true,
                gravity: "top",
                position: "center", // `left`, `center` or `right`
                className: "flex font-medium py-5 px-2 rounded-md text-white",
                escapeMarkup: false,
            }).showToast();
        },
    },
});

// Reusable
app.component("tabs", tabs);
app.component("tab", Tab);
app.component("sidebar", sidebar);
app.component("edit-assistant", editassistant);
app.component("media-content", mediacontent);
app.component("add-folder", addfolder);
app.component("media", media);
app.component("icon", icon);
app.component("breadcrumb", breadcrumb);
app.component("edit-assistant-media", editassistantmedia);
app.component("image-edit", imageedit);

app.mount(el);
