<script setup>
import { ref, computed } from 'vue';
import { ChevronRight, Folder, FolderOpen } from 'lucide-vue-next';
import addFolder from './add-folder.vue';
import editAssistant from './edit-assistant.vue';

// Self-recursion name
defineOptions({ name: 'sidebar' });

const props = defineProps({
    item: { type: Object, required: true },
    level: { type: Number, default: 0 },
    selectedId: { type: [Number, String], default: null },
});

const emit = defineEmits(['load_folder', 'load_selected_folder']);
const hasChildren = computed(() => Array.isArray(props.item?.children) && props.item.children.length > 0);
const isActive = computed(() => String(props.item?.id) === String(props.selectedId ?? ''));
// Expand root by default for discoverability
const isExpanded = ref(hasChildren.value && props.level === 0);

const reloadFolder = (value) => emit('load_folder', value);
const loadRequest = () => emit('load_selected_folder', props.item);
const toggleExpand = (e) => {
    e.stopPropagation();
    if (hasChildren.value) isExpanded.value = !isExpanded.value;
};

const onKeydown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        loadRequest();
    }
    if (e.key === 'ArrowRight' && hasChildren.value && !isExpanded.value) {
        e.preventDefault();
        isExpanded.value = true;
    }
    if (e.key === 'ArrowLeft') {
        if (hasChildren.value && isExpanded.value) {
            e.preventDefault();
            isExpanded.value = false;
        }
    }
};
</script>

<template>
        <div class="w-full select-none">
        <!-- Row -->
        <div
                class="relative flex items-center gap-2 rounded-md px-2 py-2 cursor-pointer group
                             hover:bg-base-200 transition-colors focus:outline-none"
            :style="{ paddingLeft: `${8 + level * 12}px` }"
            :title="item.name"
                @click="loadRequest"
                role="treeitem"
                tabindex="0"
                :aria-expanded="hasChildren ? isExpanded : undefined"
                :aria-selected="isActive ? 'true' : 'false'"
                @keydown="onKeydown"
                :class="[
                    isActive ? 'bg-base-200 ring-1 ring-primary/30' : '',
                ]"
        >
                <!-- Active accent bar -->
                <div v-if="isActive" class="absolute left-0 top-0 bottom-0 w-1 bg-primary rounded-r"></div>
            <!-- Disclosure -->
            <button
                v-if="hasChildren"
                @click="toggleExpand"
                    class="shrink-0 p-1.5 rounded hover:bg-base-200 focus-visible:ring-2 focus-visible:ring-primary/40"
                aria-label="Toggle children"
                :aria-expanded="isExpanded"
            >
                    <ChevronRight class="w-4 h-4 text-base-content/70 transition-transform"
                                            :class="{ 'rotate-90': isExpanded }" />
            </button>
            <div v-else class="w-6" />

                <!-- Icon -->
                <component :is="isExpanded ? FolderOpen : Folder" class="w-5 h-5 text-primary" />

            <!-- Name -->
            <div class="flex-1 min-w-0">
                    <div class="truncate text-sm font-medium text-base-content">{{ item.name }}</div>
            </div>

                <!-- Count badge -->
                <span v-if="item.count && item.count > 0"
                            class="px-1.5 py-0.5 text-[11px] rounded bg-base-200 text-base-content/70">
                    {{ item.count }}
                </span>

            <!-- Actions (show on hover) -->
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
                    <div class="tooltip" :data-tip="'New Folder'">
                        <add-folder :parent_id="item.id" @load_folder="reloadFolder" />
                    </div>
                    <div class="tooltip" :data-tip="'Options'">
                        <edit-assistant :item="item" @load_selected_folder="reloadFolder" />
                    </div>
            </div>
        </div>

        <!-- Children -->
        <TransitionGroup
            v-if="hasChildren && isExpanded"
            tag="div"
                    class="mt-1 space-y-1 border-l-2 border-base-300/70"
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <sidebar
                v-for="child in item.children"
                :key="child.id"
                :item="child"
                :level="level + 1"
                        :selected-id="selectedId"
                @load_folder="reloadFolder"
                @load_selected_folder="$emit('load_selected_folder', $event)"
            />
        </TransitionGroup>
    </div>
</template>

<style scoped>
/* Minimalist, nothing global needed */
</style>
