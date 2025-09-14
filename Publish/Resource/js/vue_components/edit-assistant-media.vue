<script setup>
import { ref } from 'vue';
import { Trash2, X } from 'lucide-vue-next';
import axios from 'axios';
import Toastify from 'toastify-js';
import { toastSuccess, showHttpError } from '../utils/notify';

const props = defineProps({
 item: {
   type: Object,
   required: true
 }
});

const isDeleting = ref(false);
const isOpen = ref(false);
const emit = defineEmits(['load_folder']);

const showDeleteModal = () => {
 isOpen.value = true;
};

const confirmDelete = async () => {
 try {
   isDeleting.value = true;
   const mode = (typeof localStorage !== 'undefined' && localStorage.getItem('magnifier.storage_mode')) || 'local';
   await axios.delete(`/file/delete/${props.item.id}`, {
     headers: { 'X-Magnifier-Mode': mode }
   });
   emit('load_folder');
  toastSuccess('File deleted successfully');
 } catch (error) {
   showHttpError(error, 'Error deleting file');
 } finally {
   isDeleting.value = false;
   isOpen.value = false;
 }
};
</script>

<template>
 <div>
   <button @click="showDeleteModal"
           class="btn btn-error">
     <Trash2 class="w-4 h-4 text-black" />
   </button>

   <Teleport to="body">
     <Transition
       enter-active-class="transition duration-300 ease-out"
       enter-from-class="translate-y-full opacity-0"
       enter-to-class="translate-y-0 opacity-100"
       leave-active-class="transition duration-200 ease-in"
       leave-from-class="translate-y-0 opacity-100"
       leave-to-class="translate-y-full opacity-0"
     >
       <div v-if="isOpen"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center px-4 py-6">
         <!-- Backdrop -->
         <div class="fixed inset-0 bg-black/30 backdrop-blur-sm"
              @click="isOpen = false"></div>

         <!-- Modal -->
         <div class="relative bg-base-100 rounded-t-2xl sm:rounded-2xl w-full max-w-sm
                     overflow-hidden shadow-xl transform transition-all">
           <!-- Header -->
           <div class="p-4 border-b border-base-200">
             <h3 class="text-lg font-semibold text-base-content">Delete File</h3>
             <button @click="isOpen = false"
                     class="absolute top-4 right-4 text-base-content/70
                            hover:text-base-content transition-colors">
               <X class="w-5 h-5" />
             </button>
           </div>

           <!-- Content -->
           <div class="p-4">
             <div class="flex items-center space-x-3 mb-4">
               <div class="w-12 h-12 rounded bg-error/10 flex items-center justify-center">
                 <Trash2 class="w-6 h-6 text-error" />
               </div>
               <div>
                 <p class="font-medium text-base-content">Delete "{{ props.item.name }}"?</p>
                 <p class="text-sm text-base-content/70">This action cannot be undone</p>
               </div>
             </div>
           </div>

           <!-- Actions -->
           <div class="flex border-t border-base-200 divide-x divide-base-200">
             <button @click="isOpen = false"
                     class="flex-1 p-4 text-center font-medium hover:bg-base-200
                            transition-colors duration-200">
               Cancel
             </button>
             <button @click="confirmDelete"
                     :disabled="isDeleting"
                     class="flex-1 p-4 text-center font-medium text-error
                            hover:bg-error/10 transition-colors duration-200
                            disabled:opacity-50">
               <span v-if="isDeleting">Deleting...</span>
               <span v-else>Delete</span>
             </button>
           </div>
         </div>
       </div>
     </Transition>
   </Teleport>
 </div>
</template>
