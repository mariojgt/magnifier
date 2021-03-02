// Load vue js
import { createApp, h } from 'vue';

import tabs from "./vue_components/Tabs";
import Tab from "./vue_components/Tab";
import sidebar from "./vue_components/side-bar.vue";
import editassistant from "./vue_components/edit-assistant.vue";
import mediacontent from "./vue_components/media-content.vue";
import addfolder from "./vue_components/add-folder.vue";
import media from "./vue_components/media.vue";
import icon from "./vue_components/icon";

const el = document.getElementById('app');

const app = createApp({});

// Reusable
app.component('tabs', tabs);
app.component('tab', Tab);
app.component('sidebar', sidebar);
app.component('edit-assistant', editassistant);
app.component('media-content', mediacontent);
app.component('add-folder', addfolder);
app.component('media', media);
app.component('icon', icon);

app.mount(el);
