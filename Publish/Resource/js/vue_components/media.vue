<!-- App.vue -->
<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import {
 Folder, Home, File, ChevronRight, Plus, Download, Trash2,
 LayoutGrid, List, Search, Settings, Moon, Sun, FolderOpen,
 Clock, HardDrive, Cloud
} from 'lucide-vue-next';
import axios from 'axios';
import addFolder from './add-folder.vue';
import sidebar from './side-bar.vue';
import pathmaker from './breadcrumb.vue';
import mediaContent from './media-content.vue';
import { showHttpError } from '../utils/notify';

const isDark = ref(false);
const searchQuery = ref('');
const sidebarWidth = ref(240);
const showPreview = ref(true);
const selectedFile = ref(null);
const selectedView = ref('grid');
// storage mode: 'local' | 's3'
const storageMode = ref(localStorage.getItem('magnifier.storage_mode') || 'local');

// apply header for backend to switch mode
axios.defaults.headers.common['X-Magnifier-Mode'] = storageMode.value;

watch(storageMode, (val) => {
  localStorage.setItem('magnifier.storage_mode', val);
  axios.defaults.headers.common['X-Magnifier-Mode'] = val;
  // reload current folder to reflect mode (URLs may differ)
  folder_target ? loadFolder(folder_target) : loadParents();
});

let folders = $ref([]);
let breadcrumb = $ref([]);
let folder_target = $ref(null);
let folder_created_at = $ref('');

// --- Recent folders (localStorage) ---
const RECENT_KEY = 'magnifier.recent_folders';
const MAX_RECENT = 8;
const recentFolders = ref(JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'));
const showingRecent = ref(false);

const pushRecent = (folder) => {
  if (!folder || !folder.id) return;
  const entry = { id: folder.id, name: folder.name, visitedAt: Date.now() };
  let list = recentFolders.value.filter(r => r.id !== folder.id);
  list.unshift(entry);
  if (list.length > MAX_RECENT) list = list.slice(0, MAX_RECENT);
  recentFolders.value = list;
  localStorage.setItem(RECENT_KEY, JSON.stringify(list));
};

const showRecent = () => {
  showingRecent.value = true;
};

const loadRecentFolder = (item) => {
  showingRecent.value = false;
  loadFolder(item.id);
  folder_target = item.id;
};

// --- Active sidebar section ---
const activeSidebarSection = ref('home'); // 'home' | 'recent'

const toggleTheme = () => {
 isDark.value = !isDark.value;
 document.documentElement.classList.toggle('dark');
};

const loadFolder = async (id) => {
 try {
  const response = await axios.get(`/folder/load/${id}`);
   folders = response.data.children;
   breadcrumb = response.data.parent;
   folder_created_at = response.data.folder_info.created_at;
   // Track in recent
   pushRecent({ id, name: response.data.folder_info?.name || breadcrumb?.[breadcrumb.length - 1]?.name || 'Folder' });
 } catch (error) {
  showHttpError(error, 'Error loading folder');
 }
};

const loadParents = async () => {
 try {
  const response = await axios.get('/folder/list');
   folders = response.data.data;
   breadcrumb = [];
   folder_target = null;
   showingRecent.value = false;
   activeSidebarSection.value = 'home';
 } catch (error) {
  showHttpError(error, 'Error loading folders');
 }
};

const reloadFolder = async (value) => {
 value?.id ? loadFolder(value.id) : loadParents();
};

const loadSelectedFolder = async (item) => {
 showingRecent.value = false;
 loadFolder(item.id);
 folder_target = item.id;
};

onMounted(() => {
 loadParents();
});
</script>

<template>
 <div :class="['h-screen flex flex-col bg-white dark:bg-gray-900', isDark ? 'dark' : '']">
   <!-- Toolbar -->
   <div class="h-11 bg-gray-50/90 dark:bg-gray-800/90 backdrop-blur-lg border-b
               border-gray-200 dark:border-gray-700 flex items-center px-3 gap-3">
     <!-- Navigation -->
     <div class="flex items-center gap-1">
       <button class="p-1 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700
                     transition-colors" @click="loadParents" title="Home">
         <Home class="w-4 h-4 text-gray-500" />
       </button>
     </div>

     <!-- Search -->
     <div class="flex-1 max-w-md">
       <div class="relative">
         <Search class="w-3.5 h-3.5 absolute left-2.5 top-2 text-gray-400" />
         <input v-model="searchQuery"
                type="text"
                placeholder="Search files..."
                class="w-full h-8 pl-8 pr-3 rounded-md bg-gray-100 dark:bg-gray-700/80
                       text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400
                       border border-transparent
                       focus:outline-none focus:ring-1 focus:ring-blue-500/50 focus:border-blue-500/50
                       transition-all" />
       </div>
     </div>

     <!-- View Controls -->
     <div class="flex items-center gap-1.5">
       <!-- Storage mode toggle -->
       <div class="flex items-center bg-gray-100 dark:bg-gray-700/60 rounded-md p-0.5">
         <button class="flex items-center gap-1 px-2 py-1 rounded text-xs transition-all"
                 :class="storageMode === 'local'
                   ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white'
                   : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                 @click="storageMode = 'local'">
           <HardDrive class="w-3 h-3" />
           Local
         </button>
         <button class="flex items-center gap-1 px-2 py-1 rounded text-xs transition-all"
                 :class="storageMode === 's3'
                   ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white'
                   : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                 @click="storageMode = 's3'">
           <Cloud class="w-3 h-3" />
           S3
         </button>
       </div>

       <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>

       <!-- View mode -->
       <div class="flex items-center bg-gray-100 dark:bg-gray-700/60 rounded-md p-0.5">
         <button class="p-1 rounded transition-all"
                 @click="selectedView = 'grid'"
                 :class="selectedView === 'grid'
                   ? 'bg-white dark:bg-gray-600 shadow-sm'
                   : 'hover:bg-white/50 dark:hover:bg-gray-600/50'">
           <LayoutGrid class="w-3.5 h-3.5" />
         </button>
         <button class="p-1 rounded transition-all"
                 @click="selectedView = 'list'"
                 :class="selectedView === 'list'
                   ? 'bg-white dark:bg-gray-600 shadow-sm'
                   : 'hover:bg-white/50 dark:hover:bg-gray-600/50'">
           <List class="w-3.5 h-3.5" />
         </button>
       </div>

       <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>

       <button class="p-1 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
               @click="toggleTheme" title="Toggle theme">
         <component :is="isDark ? Sun : Moon" class="w-3.5 h-3.5" />
       </button>
     </div>
   </div>

   <div class="flex-1 flex overflow-hidden">
     <!-- Sidebar -->
     <div :style="{ width: `${sidebarWidth}px` }"
          class="flex flex-col border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">

       <!-- Quick Access -->
       <div class="px-2 pt-2 pb-1">
         <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-2 mb-1">
           Quick Access
         </div>
         <button @click="loadParents(); activeSidebarSection = 'home'"
                 class="w-full flex items-center gap-2 px-2 py-1 rounded-md text-[13px]
                        transition-colors duration-150"
                 :class="activeSidebarSection === 'home' && !showingRecent
                   ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400'
                   : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
           <Home class="w-3.5 h-3.5" />
           <span>Home</span>
         </button>
         <button @click="showRecent(); activeSidebarSection = 'recent'"
                 class="w-full flex items-center gap-2 px-2 py-1 rounded-md text-[13px]
                        transition-colors duration-150"
                 :class="showingRecent
                   ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400'
                   : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
           <Clock class="w-3.5 h-3.5" />
           <span>Recent</span>
           <span v-if="recentFolders.length"
                 class="ml-auto text-[10px] tabular-nums text-gray-400">{{ recentFolders.length }}</span>
         </button>
       </div>

       <!-- Recent folders list (shown when Recent is active) -->
       <div v-if="showingRecent" class="flex-1 overflow-y-auto px-2 pb-2">
         <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-2 mt-2 mb-1">
           Recently Visited
         </div>
         <div v-if="recentFolders.length === 0"
              class="px-2 py-4 text-center text-xs text-gray-400">
           No recent folders yet
         </div>
         <button v-for="recent in recentFolders"
                 :key="recent.id"
                 @click="loadRecentFolder(recent)"
                 class="w-full flex items-center gap-2 px-2 py-1 rounded-md text-[13px]
                        text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50
                        transition-colors duration-150">
           <Folder class="w-3.5 h-3.5 text-gray-400 shrink-0" />
           <span class="truncate">{{ recent.name }}</span>
         </button>
       </div>

       <!-- Folders tree (shown when not viewing Recent) -->
       <div v-if="!showingRecent" class="flex-1 overflow-y-auto px-2 pb-2">
         <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-2 mt-2 mb-1">
           Folders
         </div>
         <sidebar v-for="(item, index) in folders"
                 :key="index"
                 :item="item"
                 :selected-id="folder_target"
                 @load_folder="reloadFolder"
                 @load_selected_folder="loadSelectedFolder" />
       </div>

       <!-- Add Folder -->
       <div class="px-2 py-1.5 border-t border-gray-200 dark:border-gray-700">
         <add-folder :parent_id="folder_target"
                    @load_folder="reloadFolder" />
       </div>
     </div>

     <!-- Main Content -->
     <div class="flex-1 flex flex-col min-w-0">
       <pathmaker @load_root="loadParents"
                 @load_selected_folder="loadSelectedFolder"
                 :breadcrumb="breadcrumb" />

       <media-content :parent_id="folder_target"
           :folders="folders"
           :view="selectedView"
           :search="searchQuery"
           @open-folder="loadSelectedFolder"
           @load-folder="reloadFolder"
           @select-file="selectedFile = $event"
           class="flex-1">
         <template #created>
           {{ folder_created_at }}
         </template>
       </media-content>
     </div>

     <!-- Preview Panel -->
     <div v-if="showPreview"
          class="w-72 border-l border-gray-200 dark:border-gray-700 bg-gray-50/50
                 dark:bg-gray-800/30">
       <div v-if="selectedFile" class="p-4">
         <div class="aspect-square mb-3 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-sm">
           <img :src="selectedFile.url?.medium || selectedFile.url?.default"
                :alt="selectedFile.alt || selectedFile.name"
                class="w-full h-full object-cover" />
         </div>
         <div class="space-y-3">
           <div>
             <h3 class="font-medium text-sm truncate">{{ selectedFile.name }}</h3>
             <p class="text-xs text-gray-500 mt-0.5">{{ selectedFile.media_size }}</p>
           </div>
           <div class="flex gap-2">
             <a :href="selectedFile.url?.default"
                :download="selectedFile.name"
                target="_blank"
                rel="noopener"
                class="btn btn-primary btn-sm flex-1 text-center text-xs">
               <Download class="w-3.5 h-3.5 mr-1" />
               Download
             </a>
           </div>
         </div>
       </div>
       <div v-else class="p-6 text-center text-gray-400">
         <File class="w-8 h-8 mx-auto mb-2 opacity-40" />
         <p class="text-xs">Select a file to preview</p>
       </div>
     </div>
   </div>
 </div>
</template>

<style lang="postcss">
:root {
 --background: 255 255 255;
 --foreground: 15 23 42;
}

:root.dark {
 --background: 15 23 42;
 --foreground: 241 245 249;
}

body {
 background-color: rgb(var(--background));
 color: rgb(var(--foreground));
 font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
}

.btn {
 @apply px-3 py-1.5 rounded-md transition-colors text-sm font-medium inline-flex items-center justify-center;
}

.btn-primary {
 @apply bg-blue-500 text-white hover:bg-blue-600 shadow-sm;
}

.btn-outline {
 @apply border border-gray-300 dark:border-gray-600
        hover:bg-gray-100 dark:hover:bg-gray-700;
}

.btn-sm {
 @apply px-2.5 py-1 text-xs;
}

/* Smooth scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.15);
  border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(0,0,0,0.25);
}
.dark ::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.15);
}
.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(255,255,255,0.25);
}
</style>
