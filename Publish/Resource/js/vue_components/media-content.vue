<!-- App.vue -->
<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import Toastify from 'toastify-js';
import { toast, toastError, toastSuccess, showHttpError } from '../utils/notify';
import {
 LayoutGrid, List, Upload, File, Image, FolderPlus,
 Search, Download, FileEdit, Folder, Settings, Film,
 Music, FileText, ChevronRight
} from 'lucide-vue-next';
import imageEdit from './media/image-edit.vue';
import editMedia from './edit-assistant-media.vue';
import axios from 'axios';

const props = defineProps({
 parent_id: Number,
 folders: {
   type: Array,
   default: () => []
 },
 view: {
   type: String,
   default: 'grid'
 },
 extension: {
   type: Array,
   default: () => ["jpeg", "jpg", "png", "gif", "webp"]
 },
 search: {
   type: String,
   default: ''
 }
});

// State
const emit = defineEmits(['select-file', 'open-folder', 'load-folder']);
const files = ref([]);
const uploadModalOpen = ref(false);
const isLoading = ref(false);
const selectedView = ref('grid');
watch(() => props.view, (v) => {
  selectedView.value = v === 'list' ? 'list' : 'grid';
}, { immediate: true });
const selectedFile = ref(null);
const dragActive = ref(false);
const dragCounter = ref(0);
const fileInput = ref(null);
const searchQuery = ref('');
let searchTimer = null;
const sortBy = ref('name'); // name, date, size
const sortOrder = ref('asc');

// Context menu state
const folderMenu = ref({ open: false, x: 0, y: 0, item: null });
const bgMenu = ref({ open: false, x: 0, y: 0 });
const renameModal = ref({ open: false, name: '' });
const newFolderModal = ref({ open: false, name: '' });
const busy = ref(false);

// Notifications
const showToast = (message, type = 'info') => toast(message, type);

// File handling
const getFileIcon = (ext) => {
 const type = ext.toLowerCase();
 if (['jpg','jpeg','png','gif','webp'].includes(type)) return Image;
 if (['mp4','mov','avi'].includes(type)) return Film;
 if (['mp3','wav','ogg'].includes(type)) return Music;
 return FileText;
};

const loadFiles = async () => {
  // If no folder is selected but there's a query, use global search
  if ((props.parent_id === null || props.parent_id === undefined) && searchQuery.value) {
    isLoading.value = true;
    try {
      const params = { s: searchQuery.value };
      const response = await axios.get(`/media/search`, { params });
      const list = Array.isArray(response?.data?.data) ? response.data.data : [];
      files.value = list;
    } catch (error) {
      showHttpError(error, 'Error searching files');
    }
    isLoading.value = false;
    return;
  }

  if (props.parent_id === null) return;

  isLoading.value = true;
  try {
    const params = {};
    if (searchQuery.value) params.s = searchQuery.value;
    const response = await axios.get(`/folder/files/${props.parent_id}`, { params });
    const list = Array.isArray(response?.data?.data) ? response.data.data : [];
    files.value = list;
  } catch (error) {
    showHttpError(error, 'Error loading files');
  }
  isLoading.value = false;
};

const sortFiles = computed(() => {
 const source = Array.isArray(files.value) ? files.value : [];
 return [...source].sort((a, b) => {
   let comparison = 0;
   if (sortBy.value === 'name') {
     comparison = a.name.localeCompare(b.name);
   } else if (sortBy.value === 'date') {
     comparison = new Date(a.created_at) - new Date(b.created_at);
   } else if (sortBy.value === 'size') {
     // media_size comes as humanized string; fallback to length comparison
     const toBytes = (s) => (typeof s === 'number' ? s : parseFloat(String(s))) || 0;
     comparison = toBytes(a.media_size) - toBytes(b.media_size);
   }
   return sortOrder.value === 'asc' ? comparison : -comparison;
 });
});

const filteredFiles = computed(() => {
 if (!searchQuery.value) return sortFiles.value;
 const query = searchQuery.value.toLowerCase();
 return sortFiles.value.filter(file =>
   file.name.toLowerCase().includes(query) ||
   file.ext.toLowerCase().includes(query)
 );
});

// Folders filtering (by name) sorted first by name asc
const filteredFolders = computed(() => {
  const list = Array.isArray(props.folders) ? props.folders : [];
  const q = (searchQuery.value || '').toLowerCase();
  const base = q ? list.filter(f => String(f.name || '').toLowerCase().includes(q)) : list;
  return [...base].sort((a,b) => String(a.name||'').localeCompare(String(b.name||'')));
});

const openFolder = (folder) => emit('open-folder', folder);

// Context menu helpers
const hideMenus = () => {
  folderMenu.value.open = false;
  bgMenu.value.open = false;
};
const onFolderContextMenu = (e, folder) => {
  e.preventDefault();
  e.stopPropagation();
  hideMenus();
  folderMenu.value = { open: true, x: e.clientX, y: e.clientY, item: folder };
};
const onBackgroundContextMenu = (e) => {
  // Avoid opening if a folder tile handled the event
  if (e.defaultPrevented) return;
  e.preventDefault();
  hideMenus();
  bgMenu.value = { open: true, x: e.clientX, y: e.clientY };
};

// Actions
const doRename = async () => {
  const item = folderMenu.value.item;
  if (!item || !renameModal.value.name) return;
  try {
    busy.value = true;
    await axios.post(`/folder/rename/${item.id}`, { new_name: renameModal.value.name });
    toastSuccess('Folder renamed successfully');
    emit('load-folder', props.parent_id ? { id: props.parent_id } : null);
  } catch (error) {
    showHttpError(error, 'Error renaming folder');
  } finally {
    busy.value = false;
    renameModal.value.open = false;
    hideMenus();
  }
};

const doDelete = async () => {
  const item = folderMenu.value.item;
  if (!item) return;
  try {
    busy.value = true;
    const mode = (typeof localStorage !== 'undefined' && localStorage.getItem('magnifier.storage_mode')) || 'local';
    await axios.delete(`/folder/delete/${item.id}`, { headers: { 'X-Magnifier-Mode': mode } });
    toastSuccess('Folder deleted successfully');
    emit('load-folder', props.parent_id ? { id: props.parent_id } : null);
  } catch (error) {
    showHttpError(error, 'Error deleting folder');
  } finally {
    busy.value = false;
    hideMenus();
  }
};

const doCreateFolder = async () => {
  if (!newFolderModal.value.name) return;
  try {
    busy.value = true;
    await axios.post(`/folder/create`, { name: newFolderModal.value.name, parent_id: props.parent_id });
    toastSuccess('Folder created successfully');
    emit('load-folder', props.parent_id ? { id: props.parent_id } : null);
  } catch (error) {
    showHttpError(error, 'Error creating folder');
  } finally {
    busy.value = false;
    newFolderModal.value.open = false;
    bgMenu.value.open = false;
  }
};

// Upload handling
const upload = {
 async single(file) {
   if (!props.parent_id) {
     showToast('Select a folder first', 'warning');
     return;
   }
   const formData = new FormData();
   formData.append("file", file);
   try {
     await axios.post(`/file/upload/${props.parent_id}`, formData);
     toastSuccess('File uploaded successfully');
   } catch (error) {
     showHttpError(error, 'The file failed to upload.');
     throw error;
   }
 },

 async multiple(fileList) {
   isLoading.value = true;
   let anyFailed = false;
   for (const file of fileList) {
     try {
       await upload.single(file);
     } catch (_) {
       anyFailed = true;
     }
   }
   await loadFiles();
   isLoading.value = false;
   uploadModalOpen.value = anyFailed; // keep open if something failed
 }
};

const handleFileUpload = async () => {
 if (fileInput.value?.files?.length) {
   await upload.multiple(fileInput.value.files);
 }
};

const handleFileSelect = (file) => {
 selectedFile.value = file;
 emit('select-file', file);
};

// Watchers & Lifecycle
watch(() => props.parent_id, val => {
 val === null ? files.value = [] : loadFiles();
});

// Initialize from parent prop with debounce reload
watch(() => props.search, (val) => {
  searchQuery.value = val || '';
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    if (props.parent_id !== null) {
      loadFiles();
    }
  }, 250);
}, { immediate: true });

onMounted(() => {
 if (props.parent_id !== null) {
   loadFiles();
 }
  // Global drag-and-drop listeners so dropping anywhere triggers upload
  window.addEventListener('dragover', onDragOver);
  window.addEventListener('dragenter', onDragEnter);
  window.addEventListener('dragleave', onDragLeave);
  window.addEventListener('drop', onDrop);
  window.addEventListener('dragend', onDrop); // fallback cleanup
});

onBeforeUnmount(() => {
  window.removeEventListener('dragover', onDragOver);
  window.removeEventListener('dragenter', onDragEnter);
  window.removeEventListener('dragleave', onDragLeave);
  window.removeEventListener('drop', onDrop);
  window.removeEventListener('dragend', onDrop);
});

// Drag & Drop handlers
const isFileDrag = (e) => Array.from(e?.dataTransfer?.types || []).includes('Files');
const onDragOver = (e) => {
  // Necessary to allow drop
  e.preventDefault();
};
const onDragEnter = (e) => {
  if (!isFileDrag(e)) return;
  e.preventDefault();
  dragCounter.value += 1;
  dragActive.value = true;
};
const onDragLeave = (e) => {
  if (!isFileDrag(e)) return;
  e.preventDefault();
  dragCounter.value = Math.max(0, dragCounter.value - 1);
  if (dragCounter.value === 0) dragActive.value = false;
};
const onDrop = async (e) => {
  if (!isFileDrag(e)) return;
  e.preventDefault();
  const dt = e.dataTransfer;
  dragCounter.value = 0;
  dragActive.value = false;

  if (!props.parent_id) {
    showToast('Select a folder first', 'warning');
    return;
  }
  const files = dt?.files;
  if (!files || files.length === 0) return;
  await upload.multiple(files);
};
</script>

<template>
  <div class="h-full bg-white dark:bg-gray-900">
      <!-- Header -->
      <div class="sticky top-0 z-20 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg
                  border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 flex items-center justify-between">
          <!-- View Controls -->
          <div class="flex items-center gap-3">
            <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
              <button @click="selectedView = 'grid'"
                      :class="[
                        'p-1.5 rounded-md transition-colors',
                        selectedView === 'grid'
                          ? 'bg-white dark:bg-gray-700 shadow-sm'
                          : 'hover:bg-white/50 dark:hover:bg-gray-700/50'
                      ]">
                <LayoutGrid class="w-4 h-4" />
              </button>
              <button @click="selectedView = 'list'"
                      :class="[
                        'p-1.5 rounded-md transition-colors',
                        selectedView === 'list'
                          ? 'bg-white dark:bg-gray-700 shadow-sm'
                          : 'hover:bg-white/50 dark:hover:bg-gray-700/50'
                      ]">
                <List class="w-4 h-4" />
              </button>
            </div>

            <!-- Sort -->
            <select v-model="sortBy"
                    class="px-2 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800
                           border border-gray-200 dark:border-gray-700
                           text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="name">Name</option>
              <option value="date">Date</option>
              <option value="size">Size</option>
            </select>

            <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>
            <span class="text-sm text-gray-500">
              {{ filteredFiles.length }} items
            </span>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button @click="uploadModalOpen = true"
                    class="px-3 py-1.5 bg-blue-500 text-white rounded-lg
                           hover:bg-blue-600 flex items-center gap-2
                           transition-colors">
              <Upload class="w-4 h-4" />
              <span class="text-sm font-medium">Upload</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6" @contextmenu="onBackgroundContextMenu">
        <TransitionGroup
          tag="div"
          :class="[
            selectedView === 'grid'
              ? 'grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4'
              : 'flex flex-col divide-y divide-gray-200 dark:divide-gray-700'
          ]"
          name="file-grid">

    <!-- Upload Card (Grid only) -->
          <div v-if="selectedView === 'grid'"
               key="upload"
               @click="uploadModalOpen = true"
               class="aspect-square border-2 border-dashed border-gray-200
                      dark:border-gray-700 rounded-xl hover:border-blue-500
                      transition-colors cursor-pointer flex flex-col items-center
                      justify-center group">
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-500/10 rounded-full
                        flex items-center justify-center mb-3
                        group-hover:scale-110 transition-transform">
              <Upload class="w-6 h-6 text-blue-500" />
            </div>
            <span class="text-sm font-medium">Upload Files</span>
            <span class="text-xs text-gray-500 mt-1">or drag and drop</span>
          </div>

          <!-- Folder Items (show before files) -->
    <div v-for="folder in filteredFolders"
               :key="`folder-${folder.id}`"
      @click="openFolder(folder)"
      @contextmenu.prevent="onFolderContextMenu($event, folder)"
               class="group relative bg-gray-50 dark:bg-gray-800 rounded-xl
                      overflow-hidden cursor-pointer"
               :class="[
                 selectedView === 'grid' ? 'aspect-square p-4' : 'flex items-center p-3'
               ]">
            <div :class="selectedView === 'grid' ? 'w-12 h-12 mb-3' : 'w-6 h-6'">
              <Folder class="w-full h-full text-yellow-500" />
            </div>
            <div :class="[
              'min-w-0',
              selectedView === 'grid' ? '' : 'ml-3 flex-1'
            ]">
              <div class="truncate font-medium">
                {{ folder.name }}
              </div>
              <div class="text-xs text-gray-500">
                {{ folder.count || 0 }} items
              </div>
            </div>
            <ChevronRight v-if="selectedView === 'list'"
                           class="w-4 h-4 ml-auto text-gray-400" />
          </div>

          <!-- File Items -->
          <div v-for="file in filteredFiles"
               :key="file.id"
               @click="handleFileSelect(file)"
               class="group relative bg-gray-50 dark:bg-gray-800 rounded-xl
                      overflow-hidden cursor-pointer"
               :class="[
                 selectedView === 'grid' ? 'aspect-square' : 'flex items-center p-3',
                 selectedFile?.id === file.id ? 'ring-2 ring-blue-500' : ''
               ]">

            <!-- Thumbnail/Icon -->
            <div :class="selectedView === 'grid' ? 'aspect-square' : 'w-12 h-12'">
              <img v-if="extension.includes(file.ext)"
                   :src="file.url.default"
                   :alt="file.name"
                   class="w-full h-full object-cover rounded" />
              <div v-else
                   class="w-full h-full flex items-center justify-center">
                <component :is="getFileIcon(file.ext)"
                          class="w-8 h-8 text-gray-400" />
              </div>
            </div>

            <!-- Info -->
            <div :class="[
              'flex flex-col min-w-0',
              selectedView === 'grid'
                ? 'absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/60'
                : 'flex-1 ml-4'
            ]">
              <span :class="[
                'font-medium truncate',
                selectedView === 'grid' ? 'text-white' : ''
              ]">{{ file.name }}</span>
              <span :class="[
                'text-sm',
                selectedView === 'grid' ? 'text-gray-300' : 'text-gray-500'
              ]">
                {{ file.ext.toUpperCase() }} • {{ file.media_size }}
              </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1"
                 :class="[
                   selectedView === 'grid'
                     ? 'absolute top-2 right-2 opacity-0 group-hover:opacity-100'
                     : 'ml-auto',
                   'transition-opacity'
                 ]">
              <a :href="file.url.default"
                 @click.stop
                 target="_blank"
                 class="p-1.5 rounded-lg hover:bg-white/10">
                <Download class="w-4 h-4" :class="selectedView === 'grid' ? 'text-white' : ''" />
              </a>
              <edit-media
                @click.stop
                @load_folder="loadFiles"
                :item="file">
                <FileEdit class="w-4 h-4" :class="selectedView === 'grid' ? 'text-white' : ''" />
              </edit-media>
            </div>
          </div>
        </TransitionGroup>

        <!-- Empty State -->
        <div v-if="!filteredFiles.length && !isLoading"
             class="flex flex-col items-center justify-center h-64">
          <File class="w-12 h-12 text-gray-400 mb-4" />
          <h3 class="text-lg font-medium">No files found</h3>
          <p class="text-sm text-gray-500 mt-1">
            Upload files or try a different search
          </p>
        </div>
      </div>

      <!-- Context Menus -->
      <Teleport to="body">
        <!-- Backdrop for closing menus -->
        <div v-if="folderMenu.open || bgMenu.open"
             class="fixed inset-0 z-40" @click="hideMenus" />

        <!-- Folder menu -->
        <div v-if="folderMenu.open"
             class="fixed z-50 w-48 rounded-md shadow-lg ring-1 ring-black/10 bg-base-100"
             :style="{ left: folderMenu.x + 'px', top: folderMenu.y + 'px' }">
          <button class="w-full text-left px-3 py-2 hover:bg-base-200"
                  @click="openFolder(folderMenu.item); hideMenus()">Open</button>
          <button class="w-full text-left px-3 py-2 hover:bg-base-200"
                  @click="renameModal = { open: true, name: folderMenu.item?.name || '' }">Rename</button>
          <button class="w-full text-left px-3 py-2 text-error hover:bg-error/10"
                  @click="doDelete">Delete</button>
        </div>

        <!-- Background menu -->
        <div v-if="bgMenu.open"
             class="fixed z-50 w-48 rounded-md shadow-lg ring-1 ring-black/10 bg-base-100"
             :style="{ left: bgMenu.x + 'px', top: bgMenu.y + 'px' }">
          <button class="w-full text-left px-3 py-2 hover:bg-base-200"
                  @click="newFolderModal = { open: true, name: '' }">New Folder</button>
        </div>

        <!-- Rename Modal -->
        <div v-if="renameModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" @click="renameModal.open = false" />
          <div class="relative bg-base-100 rounded-2xl w-full max-w-sm shadow-xl">
            <div class="p-4 border-b border-base-200 font-semibold">Rename Folder</div>
            <div class="p-4">
              <input class="input input-bordered w-full" v-model="renameModal.name" @keyup.enter="doRename" placeholder="Enter new name" />
            </div>
            <div class="flex justify-end gap-2 p-4 border-t border-base-200">
              <button class="btn btn-ghost" @click="renameModal.open = false">Cancel</button>
              <button class="btn btn-primary" :disabled="!renameModal.name || busy" @click="doRename">Rename</button>
            </div>
          </div>
        </div>

        <!-- New Folder Modal -->
        <div v-if="newFolderModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" @click="newFolderModal.open = false" />
          <div class="relative bg-base-100 rounded-2xl w-full max-w-sm shadow-xl">
            <div class="p-4 border-b border-base-200 font-semibold">New Folder</div>
            <div class="p-4">
              <input class="input input-bordered w-full" v-model="newFolderModal.name" @keyup.enter="doCreateFolder" placeholder="Enter folder name" />
            </div>
            <div class="flex justify-end gap-2 p-4 border-t border-base-200">
              <button class="btn btn-ghost" @click="newFolderModal.open = false">Cancel</button>
              <button class="btn btn-primary" :disabled="!newFolderModal.name || busy" @click="doCreateFolder">Create</button>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Modal and Overlays -->
  <div v-if="dragActive"
           class="fixed inset-0 z-50 backdrop-blur-sm bg-black/20
                  flex items-center justify-center">
        <!-- Drag & Drop Overlay -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl
                    max-w-lg w-full mx-4 text-center">
          <div class="w-20 h-20 bg-blue-50 dark:bg-blue-500/10 rounded-full
                      flex items-center justify-center mx-auto mb-4">
            <Upload class="w-10 h-10 text-blue-500 animate-bounce" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            Drop files to upload
          </h3>
          <p class="text-gray-500 dark:text-gray-400">
            Release to upload your files to the current folder
          </p>
        </div>
      </div>

      <!-- Upload Modal -->
      <dialog class="modal modal-bottom sm:modal-middle"
              :open="uploadModalOpen">
        <div class="modal-box max-w-2xl">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-50 dark:bg-blue-500/10 rounded-full
                          flex items-center justify-center">
                <Upload class="w-5 h-5 text-blue-500" />
              </div>
              <h3 class="text-xl font-semibold">Upload Files</h3>
            </div>
            <button @click="uploadModalOpen = false"
                    class="btn btn-ghost btn-sm btn-circle">×</button>
          </div>

          <div class="space-y-4">
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600
                        rounded-xl p-8 text-center">
              <input type="file"
                     ref="fileInput"
                     multiple
                     class="hidden"
                     @change="handleFileUpload" />
              <button @click="fileInput?.click()"
                      class="px-4 py-2 bg-blue-500 text-white rounded-lg
                             hover:bg-blue-600 mb-3">
                Choose Files
              </button>
              <p class="text-sm text-gray-500">
                or drag and drop files here
              </p>
            </div>

            <div class="flex justify-end gap-3">
              <button @click="uploadModalOpen = false"
                      class="btn btn-ghost">
                Cancel
              </button>
              <button @click="handleFileUpload"
                      :disabled="isLoading || !fileInput?.files?.length"
                      class="btn btn-primary">
                <span v-if="isLoading" class="loading loading-spinner loading-sm" />
                {{ isLoading ? 'Uploading...' : 'Upload' }}
              </button>
            </div>
          </div>
        </div>
        <div class="modal-backdrop bg-black/50" @click="uploadModalOpen = false" />
      </dialog>

      <!-- Loading Overlay -->
      <div v-if="isLoading"
           class="fixed inset-0 bg-black/20 backdrop-blur-sm
                  flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl text-center">
          <div class="loading loading-spinner loading-lg mb-4" />
          <h3 class="text-lg font-semibold">Processing Files</h3>
          <p class="text-sm text-gray-500 mt-2">Please wait a moment...</p>
        </div>
      </div>
    </div>
  </template>

<style scoped lang="postcss">
.file-grid-move {
 transition: all 0.3s ease;
}

.file-grid-enter-active,
.file-grid-leave-active {
 transition: all 0.3s ease;
}

.file-grid-enter-from,
.file-grid-leave-to {
 opacity: 0;
 transform: scale(0.95);
}

.loading {
 @apply animate-spin rounded-full border-4 border-blue-500 border-r-transparent;
}

@keyframes bounce {
 0%, 100% {
   transform: translateY(-25%);
   animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
 }
 50% {
   transform: translateY(0);
   animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
 }
}
</style>
