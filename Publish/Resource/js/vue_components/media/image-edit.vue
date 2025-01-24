<script setup>
import { ref, watch } from 'vue';
import { X, Save, ZoomIn, Edit3 } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
 item: {
   type: Object,
   required: true
 }
});

const emit = defineEmits(['loading', 'load_file']);
const isOpen = ref(false);
const isLoading = ref(false);

const formData = ref({
 title: props.item.title || '',
 alt: props.item.alt || '',
 caption: props.item.caption || '',
 description: props.item.description || ''
});

const updateImage = async () => {
 try {
   isLoading.value = true;
   emit('loading');

   await axios.post(`/file/update/${props.item.id}`, formData.value);
   emit('load_file');
   isOpen.value = false;

 } finally {
   isLoading.value = false;
   emit('loading');
 }
};

watch(() => props.item, (newItem) => {
 formData.value = {
   title: newItem.title || '',
   alt: newItem.alt || '',
   caption: newItem.caption || '',
   description: newItem.description || ''
 };
}, { deep: true });
</script>

<template>
 <div>
   <!-- Image Thumbnail -->
   <div class="relative group cursor-pointer" @click="isOpen = true">
     <img :src="props.item.url.default"
          :alt="props.item.alt"
          class="rounded-lg transition-transform duration-200
                 group-hover:scale-105" />

     <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100
                 transition-opacity duration-200 flex items-center justify-center">
       <Edit3 class="w-8 h-8 text-white" />
     </div>
   </div>

   <!-- Edit Modal -->
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
         <div class="fixed inset-0 bg-black/70 backdrop-blur-sm"
              @click="isOpen = false" />

         <!-- Modal -->
         <div class="relative bg-base-100 rounded-2xl w-full max-w-2xl
                     overflow-hidden shadow-xl">
           <!-- Header -->
           <div class="flex items-center justify-between p-4 border-b border-base-200">
             <h3 class="text-lg font-semibold flex items-center gap-2">
               <Edit3 class="w-5 h-5 text-primary" />
               Edit Image Details
             </h3>
             <button @click="isOpen = false"
                     class="btn btn-ghost btn-sm btn-circle">
               <X class="w-4 h-4" />
             </button>
           </div>

           <!-- Content -->
           <div class="p-4">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <!-- Image Preview -->
               <div class="relative group">
                 <img :src="props.item.url.default"
                      :alt="formData.alt"
                      class="w-full rounded-lg" />
                 <a :href="props.item.url.default"
                    target="_blank"
                    class="absolute inset-0 bg-black/50 opacity-0
                           group-hover:opacity-100 transition-opacity
                           flex items-center justify-center">
                   <ZoomIn class="w-8 h-8 text-white" />
                 </a>
               </div>

               <!-- Form -->
               <div class="space-y-4">
                 <div v-for="(value, key) in formData" :key="key">
                   <label :for="key" class="text-sm font-medium block mb-1 capitalize">
                     {{ key }}
                   </label>
                   <input :id="key"
                          v-model="formData[key]"
                          type="text"
                          :placeholder="`Enter ${key}`"
                          class="input input-bordered w-full" />
                 </div>
               </div>
             </div>
           </div>

           <!-- Footer -->
           <div class="flex justify-end gap-2 p-4 border-t border-base-200">
             <button @click="isOpen = false"
                     class="btn btn-ghost">
               Cancel
             </button>
             <button @click="updateImage"
                     :disabled="isLoading"
                     class="btn btn-primary">
               <Save v-if="!isLoading" class="w-4 h-4 mr-2" />
               <span v-if="isLoading" class="loading loading-spinner loading-sm" />
               {{ isLoading ? 'Saving...' : 'Save Changes' }}
             </button>
           </div>
         </div>
       </div>
     </Transition>
   </Teleport>
 </div>
</template>
