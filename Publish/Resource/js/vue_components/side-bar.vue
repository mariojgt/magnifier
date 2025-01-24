<script setup>
import { ref } from 'vue';
import {
    Folder,
    FolderPlus,
    ChevronRight,
    Calendar,
    Plus,
    Edit,
    ArrowRight
} from 'lucide-vue-next';
import addFolder from './add-folder.vue';
import editAssistant from './edit-assistant.vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
});

const isExpanded = ref(false);
const emit = defineEmits(["load_folder", "load_selected_folder"]);

const reloadFolder = (value) => emit('load_folder', value);
const loadRequest = () => emit('load_selected_folder', props.item);
</script>

<template>
    <div class="group flex items-center justify-between p-2 rounded-lg
                hover:bg-base-200 transition-all duration-200 relative">
        <!-- Main folder content -->
        <div class="flex items-center flex-1 cursor-pointer" @click="loadRequest">
            <div class="hover-reveal">
                <!-- Background effect -->
                <div class="absolute inset-0 bg-primary/10 transform origin-left scale-x-0
                      group-hover:scale-x-100 transition-transform duration-300" />

                <!-- Folder icon -->
                <div class="mr-2 transform group-hover:scale-110 transition-transform duration-200">
                    <FolderPlus v-if="item.count >= 1" class="w-5 h-5 text-primary" />
                    <Folder v-else class="w-5 h-5 text-primary/70" />
                </div>

                <!-- Folder details -->
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-base-content">{{ item.name }}</div>
                    <div class="flex items-center text-xs text-base-content/70">
                        <Calendar class="w-3 h-3 mr-1" />
                        {{ item.created_at }}
                    </div>
                </div>

                <ChevronRight class="w-4 h-4 text-base-content/50 transition-transform
                             duration-200 group-hover:translate-x-1" />
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2" @click.stop> <!-- Add @click.stop here -->
            <add-folder @load_folder="reloadFolder" :parent_id="item.id">
                <Plus class="w-4 h-4" />
            </add-folder>

            <edit-assistant @load_selected_folder="reloadFolder" :item="item">
                <Edit class="w-4 h-4" />
            </edit-assistant>
        </div>
    </div>

    <!-- Nested folders -->
    <TransitionGroup v-if="item.children?.length" class="pl-6 space-y-1 border-l-2 border-base-200 ml-3"
        enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-4">
        <div v-for="child in item.children" :key="child.id">
            <sidebar-item :item="child" @load_folder="reloadFolder"
                @load_selected_folder="$emit('load_selected_folder', $event)" />
        </div>
    </TransitionGroup>
</template>

<style scoped>
.folder-enter-active,
.folder-leave-active {
    transition: all 0.3s ease;
}

.folder-enter-from,
.folder-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}
</style>
