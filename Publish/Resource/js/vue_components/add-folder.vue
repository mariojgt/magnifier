<script setup>
import { ref } from 'vue';
import { Plus, FolderPlus, X } from 'lucide-vue-next';
import Toastify from 'toastify-js';
import axios from 'axios';

const props = defineProps({
 parent_id: Number
});

const isOpen = ref(false);
const folderName = ref('');
const isLoading = ref(false);
const emit = defineEmits(['load_folder']);

const createFolder = async () => {
 if (!folderName.value) return;

 try {
   isLoading.value = true;
   await axios.post("folder/create", {
     name: folderName.value,
     parent_id: props.parent_id,
   });

   emit("load_folder", { id: props.parent_id });
   Toastify({
     text: "Folder created successfully",
     style: { background: "#10B981" }
   }).showToast();

   folderName.value = '';
   isOpen.value = false;

 } catch (error) {
   if (error.response?.data?.errors) {
     Object.values(error.response.data.errors).forEach(msg =>
       Toastify({ text: msg, style: { background: "#EF4444" } }).showToast()
     );
   }
 } finally {
   isLoading.value = false;
 }
};
</script>

<template>
 <div>
   <button @click="isOpen = true"
           class="btn btn-ghost btn-circle hover:bg-primary/10">
     <Plus class="w-5 h-5 text-primary" />
   </button>

   <Teleport to="body">
     <Transition
       enter-active-class="transition duration-300 ease-out"
       enter-from-class="opacity-0 scale-95"
       enter-to-class="opacity-100 scale-100"
       leave-active-class="transition duration-200 ease-in"
       leave-from-class="opacity-100 scale-100"
       leave-to-class="opacity-0 scale-95"
     >
       <div v-if="isOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
         <!-- Backdrop -->
         <div class="fixed inset-0 bg-black/30 backdrop-blur-sm"
              @click="isOpen = false"></div>

         <!-- Modal -->
         <div class="relative bg-base-100 rounded-2xl w-full max-w-sm
                     overflow-hidden shadow-xl">
           <!-- Header -->
           <div class="flex items-center justify-between p-4 border-b border-base-200">
             <div class="flex items-center gap-2">
               <FolderPlus class="w-5 h-5 text-primary" />
               <h3 class="text-lg font-semibold">New Folder</h3>
             </div>
             <button @click="isOpen = false"
                     class="btn btn-ghost btn-sm btn-circle">
               <X class="w-4 h-4" />
             </button>
           </div>

           <!-- Content -->
           <div class="p-4">
             <input type="text"
                    v-model="folderName"
                    @keyup.enter="createFolder"
                    placeholder="Enter folder name"
                    class="input input-bordered w-full"
                    autofocus />
           </div>

           <!-- Footer -->
           <div class="flex justify-end gap-2 p-4 border-t border-base-200">
             <button @click="isOpen = false"
                     class="btn btn-ghost">
               Cancel
             </button>
             <button @click="createFolder"
                     :disabled="!folderName || isLoading"
                     class="btn btn-primary">
               <span v-if="isLoading">Creating...</span>
               <span v-else>Create Folder</span>
             </button>
           </div>
         </div>
       </div>
     </Transition>
   </Teleport>
 </div>
</template>
