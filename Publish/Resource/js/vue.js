// Load vue js
import { createApp } from 'vue/dist/vue.esm-bundler';

import sidebar from "./vue_components/side-bar.vue";
import editassistant from "./vue_components/edit-assistant.vue";
import mediacontent from "./vue_components/media-content.vue";
import addfolder from "./vue_components/add-folder.vue";
import media from "./vue_components/media.vue";
import icon from "./vue_components/icon.vue";
import editassistantmedia from "./vue_components/edit-assistant-media.vue";
import pathmake from "./vue_components/breadcrumb.vue";
// image edit
import imageedit from "./vue_components/media_edit/image-edit.vue";

const el = document.getElementById("app");

const app = createApp({});

// Reusable
app.component("sidebar", sidebar);
app.component("edit-assistant", editassistant);
app.component("media-content", mediacontent);
app.component("add-folder", addfolder);
app.component("media", media);
app.component("icon", icon);
app.component("edit-assistant-media", editassistantmedia);
app.component("image-edit", imageedit);
app.component("pathmaker", pathmake);

app.mount(el);
