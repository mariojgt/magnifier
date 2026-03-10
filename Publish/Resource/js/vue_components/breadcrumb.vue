<script setup>
import { ref } from 'vue';
import { Home, ChevronRight, Folder } from 'lucide-vue-next';

const props = defineProps({
  breadcrumb: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['load_root', 'load_selected_folder']);
const hoveredIndex = ref(null);

const loadParents = () => emit('load_root');
const loadFolder = (item) => emit('load_selected_folder', item);
</script>

<template>
 <nav class="flex items-center py-1.5 px-3 bg-gray-50/80 dark:bg-gray-800/40
             border-b border-gray-100 dark:border-gray-700/50 overflow-x-auto">
   <!-- Home -->
   <button @click="loadParents"
           class="flex items-center min-w-fit px-1.5 py-0.5 rounded-md text-[13px]
                  transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700/50">
     <Home class="w-3.5 h-3.5 text-gray-500" />
     <span class="ml-1.5 font-medium text-gray-600 dark:text-gray-400">Home</span>
   </button>

   <!-- Separator after home -->
   <ChevronRight v-if="breadcrumb?.length"
                 class="w-3 h-3 mx-1 text-gray-300 dark:text-gray-600 shrink-0" />

   <!-- Breadcrumb items -->
   <div class="flex items-center gap-0.5 overflow-x-auto">
     <template v-for="(item, index) in breadcrumb" :key="index">
       <button @click="loadFolder(item)"
               class="flex items-center min-w-fit px-1.5 py-0.5 rounded-md text-[13px]
                      transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700/50
                      whitespace-nowrap"
               :class="index === breadcrumb.length - 1
                 ? 'text-gray-900 dark:text-gray-100 font-medium'
                 : 'text-gray-500 dark:text-gray-400'">
         <Folder class="w-3.5 h-3.5 mr-1.5 text-gray-400" />
         <span>{{ item.name }}</span>
       </button>

       <ChevronRight v-if="index < breadcrumb.length - 1"
                    class="w-3 h-3 text-gray-300 dark:text-gray-600 shrink-0" />
     </template>
   </div>
 </nav>
</template>

<style scoped>
</style>
