<!-- App.vue -->
<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import Toastify from 'toastify-js';
import {
 LayoutGrid, List, Upload, File, Image, FolderPlus,
 Search, Download, FileEdit, Folder, Settings, Film,
 Music, FileText
} from 'lucide-vue-next';
import imageEdit from './media/image-edit.vue';
import editMedia from './edit-assistant-media.vue';
import axios from 'axios';

const props = defineProps({
 parent_id: Number,
 extension: {
   type: Array,
   default: () => ["jpeg", "jpg", "png", "gif", "webp"]
 }
});

// State
const emit = defineEmits(['select-file']);
const files = ref([]);
const uploadModalOpen = ref(false);
const isLoading = ref(false);
const selectedView = ref('grid');
const selectedFile = ref(null);
const dragActive = ref(false);
const fileInput = ref(null);
const searchQuery = ref('');
const sortBy = ref('name'); // name, date, size
const sortOrder = ref('asc');

// Notifications
const showToast = (message, type = 'info') => {
 Toastify({
   text: message,
   duration: 3000,
   position: "top-right",
   style: {
     background: type === 'error' ? "#ef4444" :
                type === 'warning' ? "#f59e0b" : "#3b82f6"
   }
 }).showToast();
};

// File handling
const getFileIcon = (ext) => {
 const type = ext.toLowerCase();
 if (['jpg','jpeg','png','gif','webp'].includes(type)) return Image;
 if (['mp4','mov','avi'].includes(type)) return Film;
 if (['mp3','wav','ogg'].includes(type)) return Music;
 return FileText;
};

const loadFiles = async () => {
 if (props.parent_id === null) return;

 isLoading.value = true;
 try {
   const response = await axios.get(`/folder/files/${props.parent_id}`);
   files.value = response.data.data;
 } catch (error) {
   showToast('Error loading files', 'error');
 }
 isLoading.value = false;
};

const sortFiles = computed(() => {
 return [...files.value].sort((a, b) => {
   let comparison = 0;
   if (sortBy.value === 'name') {
     comparison = a.name.localeCompare(b.name);
   } else if (sortBy.value === 'date') {
     comparison = new Date(a.created_at) - new Date(b.created_at);
   } else if (sortBy.value === 'size') {
     comparison = parseInt(a.size) - parseInt(b.size);
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
     showToast('File uploaded successfully', 'success');
   } catch (error) {
     if (error.response?.data?.errors) {
       Object.values(error.response.data.errors)
         .forEach(msg => showToast(msg, 'error'));
     }
   }
 },

 async multiple(fileList) {
   isLoading.value = true;
   for (const file of fileList) {
     await upload.single(file);
   }
   await loadFiles();
   isLoading.value = false;
   uploadModalOpen.value = false;
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

onMounted(() => {
 if (props.parent_id !== null) {
   loadFiles();
 }
});
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
      <div class="p-6">
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

<style scoped>
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
