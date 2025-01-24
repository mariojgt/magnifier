<script setup>
import { ref } from 'vue';
import {
 Edit,
 Trash2,
 X,
 MoreVertical,
 RefreshCw,
 FolderEdit
} from 'lucide-vue-next';
import Toastify from 'toastify-js';
import axios from 'axios';

const props = defineProps({
 item: {
   type: Object,
   required: true
 }
});

const isDeleteModalOpen = ref(false);
const isRenameModalOpen = ref(false);
const isMenuOpen = ref(false);
const folderName = ref(props.item?.name || '');
const isLoading = ref(false);

const emit = defineEmits(['load_selected_folder']);

const handleRename = async () => {
 if (!folderName.value) return;

 try {
   isLoading.value = true;
   await axios.post(`/folder/rename/${props.item.id}`, {
     new_name: folderName.value
   });

   emit('load_selected_folder', props.item);
   Toastify({
     text: "Folder renamed successfully",
     style: { background: "#10B981" }
   }).showToast();

   isRenameModalOpen.value = false;
 } catch (error) {
   Toastify({
     text: "Error renaming folder",
     style: { background: "#EF4444" }
   }).showToast();
 } finally {
   isLoading.value = false;
 }
};

const handleDelete = async () => {
 try {
   isLoading.value = true;
   await axios.delete(`/folder/delete/${props.item.id}`);

   emit('load_selected_folder', null);
   Toastify({
     text: "Folder deleted successfully",
     style: { background: "#10B981" }
   }).showToast();

   isDeleteModalOpen.value = false;
 } catch (error) {
   Toastify({
     text: "Error deleting folder",
     style: { background: "#EF4444" }
   }).showToast();
 } finally {
   isLoading.value = false;
 }
};
</script>

<template>
 <div class="relative">
   <!-- Menu Trigger -->
   <button @click="isMenuOpen = !isMenuOpen"
           class="btn btn-ghost btn-circle btn-sm">
     <MoreVertical class="w-4 h-4" />
   </button>

   <!-- Dropdown Menu -->
   <Transition
     enter-active-class="transition duration-200 ease-out"
     enter-from-class="transform scale-95 opacity-0"
     enter-to-class="transform scale-100 opacity-100"
     leave-active-class="transition duration-150 ease-in"
     leave-from-class="transform scale-100 opacity-100"
     leave-to-class="transform scale-95 opacity-0"
   >
     <div v-if="isMenuOpen"
          class="absolute right-0 mt-2 w-48 rounded-lg bg-base-100
                 shadow-lg ring-1 ring-base-content/5 z-50">
       <div class="py-1">
         <button @click="isRenameModalOpen = true; isMenuOpen = false"
                 class="flex w-full items-center px-4 py-2 text-sm
                        hover:bg-base-200 transition-colors">
           <FolderEdit class="w-4 h-4 mr-2" />
           Rename
         </button>
         <button @click="isDeleteModalOpen = true; isMenuOpen = false"
                 class="flex w-full items-center px-4 py-2 text-sm text-error
                        hover:bg-error/10 transition-colors">
           <Trash2 class="w-4 h-4 mr-2" />
           Delete
         </button>
       </div>
     </div>
   </Transition>

   <!-- Rename Modal -->
   <Teleport to="body">
     <Transition
       enter-active-class="transition duration-300 ease-out"
       enter-from-class="opacity-0 scale-95"
       enter-to-class="opacity-100 scale-100"
       leave-active-class="transition duration-200 ease-in"
       leave-from-class="opacity-100 scale-100"
       leave-to-class="opacity-0 scale-95"
     >
       <div v-if="isRenameModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
         <div class="fixed inset-0 bg-black/30 backdrop-blur-sm"
              @click="isRenameModalOpen = false"></div>

         <div class="relative bg-base-100 rounded-2xl w-full max-w-sm shadow-xl">
           <div class="p-4 border-b border-base-200">
             <div class="flex items-center gap-2">
               <FolderEdit class="w-5 h-5 text-primary" />
               <h3 class="text-lg font-semibold">Rename Folder</h3>
             </div>
             <button @click="isRenameModalOpen = false"
                     class="absolute top-4 right-4 btn btn-ghost btn-sm btn-circle">
               <X class="w-4 h-4" />
             </button>
           </div>

           <div class="p-4">
             <input type="text"
                    v-model="folderName"
                    @keyup.enter="handleRename"
                    class="input input-bordered w-full"
                    placeholder="Enter new name" />
           </div>

           <div class="flex justify-end gap-2 p-4 border-t border-base-200">
             <button @click="isRenameModalOpen = false"
                     class="btn btn-ghost">Cancel</button>
             <button @click="handleRename"
                     :disabled="!folderName || isLoading"
                     class="btn btn-primary">
               <RefreshCw v-if="isLoading" class="w-4 h-4 animate-spin" />
               <span v-else>Rename</span>
             </button>
           </div>
         </div>
       </div>
     </Transition>
   </Teleport>

   <!-- Delete Modal -->
   <Teleport to="body">
     <Transition
       enter-active-class="transition duration-300 ease-out"
       enter-from-class="opacity-0 scale-95"
       enter-to-class="opacity-100 scale-100"
       leave-active-class="transition duration-200 ease-in"
       leave-from-class="opacity-100 scale-100"
       leave-to-class="opacity-0 scale-95"
     >
       <div v-if="isDeleteModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
         <div class="fixed inset-0 bg-black/30 backdrop-blur-sm"
              @click="isDeleteModalOpen = false"></div>

         <div class="relative bg-base-100 rounded-2xl w-full max-w-sm shadow-xl">
           <div class="p-4 border-b border-base-200">
             <div class="flex items-center gap-2">
               <Trash2 class="w-5 h-5 text-error" />
               <h3 class="text-lg font-semibold">Delete Folder</h3>
             </div>
             <button @click="isDeleteModalOpen = false"
                     class="absolute top-4 right-4 btn btn-ghost btn-sm btn-circle">
               <X class="w-4 h-4" />
             </button>
           </div>

           <div class="p-4">
             <p class="text-base-content/70">
               This will permanently delete "{{ props.item.name }}" and all its contents.
               This action cannot be undone.
             </p>
           </div>

           <div class="flex justify-end gap-2 p-4 border-t border-base-200">
             <button @click="isDeleteModalOpen = false"
                     class="btn btn-ghost">Cancel</button>
             <button @click="handleDelete"
                     :disabled="isLoading"
                     class="btn btn-error">
               <RefreshCw v-if="isLoading" class="w-4 h-4 animate-spin" />
               <span v-else>Delete</span>
             </button>
           </div>
         </div>
       </div>
     </Transition>
   </Teleport>
 </div>
</template>
