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
 <nav class="flex items-center py-2 px-4 bg-base-200/50 backdrop-blur
             rounded-lg shadow-sm overflow-x-auto">
   <!-- Home -->
   <button @click="loadParents"
           @mouseenter="hoveredIndex = -1"
           @mouseleave="hoveredIndex = null"
           class="flex items-center min-w-fit px-2 py-1 rounded-md
                  transition-all duration-200 hover:bg-primary/10
                  relative group">
     <Home class="w-4 h-4 text-primary" />
     <span class="ml-2 text-sm font-medium">Home</span>

     <div v-if="hoveredIndex === -1"
          class="absolute inset-0 border-2 border-primary/30
                 rounded-md scale-105 animate-pulse"/>
   </button>

   <!-- Separator after home -->
   <ChevronRight v-if="breadcrumb?.length"
                 class="w-4 h-4 mx-2 text-base-content/30" />

   <!-- Breadcrumb items -->
   <div class="flex items-center space-x-2 overflow-x-auto">
     <template v-for="(item, index) in breadcrumb" :key="index">
       <button @click="loadFolder(item)"
               @mouseenter="hoveredIndex = index"
               @mouseleave="hoveredIndex = null"
               class="flex items-center min-w-fit px-2 py-1 rounded-md
                      transition-all duration-200 hover:bg-primary/10
                      relative group whitespace-nowrap">
         <Folder class="w-4 h-4 text-primary/70 mr-2" />
         <span class="text-sm font-medium">{{ item.name }}</span>

         <div v-if="hoveredIndex === index"
              class="absolute inset-0 border-2 border-primary/30
                     rounded-md scale-105 animate-pulse"/>
       </button>

       <ChevronRight v-if="index < breadcrumb.length - 1"
                    class="w-4 h-4 text-base-content/30 shrink-0" />
     </template>
   </div>
 </nav>
</template>

<style scoped>
.animate-pulse {
 animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
 0%, 100% { opacity: 1; }
 50% { opacity: .5; }
}
</style>
