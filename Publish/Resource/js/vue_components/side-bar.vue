<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
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

// Windows-like click vs double-click behavior
const clickTimeout = ref(null);
const handleSingleClick = () => {
    // Select/open (existing behavior)
    loadRequest();
};
const handleDoubleClick = (e) => {
    if (hasChildren.value) {
        if (e) e.stopPropagation();
        isExpanded.value = !isExpanded.value;
    } else {
        loadRequest();
    }
};
const onClick = (e) => {
    if (clickTimeout.value) {
        clearTimeout(clickTimeout.value);
        clickTimeout.value = null;
        handleDoubleClick(e);
    } else {
        clickTimeout.value = setTimeout(() => {
            handleSingleClick();
            clickTimeout.value = null;
        }, 180);
    }
};
onBeforeUnmount(() => {
    if (clickTimeout.value) clearTimeout(clickTimeout.value);
});

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
            class="tree-row relative flex items-center gap-1.5 rounded-md px-2 py-1 cursor-pointer group
                   hover:bg-base-200/60 transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            :style="{ '--level': level }"
            :title="item.name"
            @click="onClick"
            @dblclick.stop.prevent
            role="treeitem"
            tabindex="0"
            :aria-level="level + 1"
            :aria-expanded="hasChildren ? isExpanded : undefined"
            :aria-selected="isActive ? 'true' : 'false'"
            @keydown="onKeydown"
            :class="[
                isActive ? 'bg-primary/10 text-primary font-semibold' : '',
                level === 0 ? 'is-root' : ''
            ]"
        >
            <!-- Active accent bar -->
            <div v-if="isActive" class="absolute left-0 top-1 bottom-1 w-0.5 bg-primary rounded-r"></div>

            <!-- Disclosure -->
            <button
                v-if="hasChildren"
                @click="toggleExpand"
                class="shrink-0 p-0.5 rounded hover:bg-base-300/50 focus-visible:ring-2 focus-visible:ring-primary/40"
                aria-label="Toggle children"
                :aria-expanded="isExpanded"
            >
                <ChevronRight class="w-3 h-3 text-base-content/50 transition-transform duration-150"
                              :class="{ 'rotate-90': isExpanded }" />
            </button>
            <div v-else class="w-4" />

            <!-- Icon -->
            <component :is="isExpanded ? FolderOpen : Folder"
                       class="w-4 h-4 shrink-0"
                       :class="isActive ? 'text-primary' : 'text-base-content/50'" />

            <!-- Name -->
            <div class="flex-1 min-w-0">
                <div class="truncate text-[13px] leading-tight"
                     :class="isActive ? '' : 'text-base-content/80'">{{ item.name }}</div>
            </div>

            <!-- Count badge -->
            <span v-if="item.count && item.count > 0"
                  class="px-1 py-px text-[10px] leading-none rounded-full bg-base-200 text-base-content/50 tabular-nums">
                {{ item.count }}
            </span>

            <!-- Actions (show on hover) -->
            <div class="flex items-center opacity-0 group-hover:opacity-100 transition-opacity duration-150" @click.stop>
                <add-folder :parent_id="item.id" @load_folder="reloadFolder" />
                <edit-assistant :item="item" @load_selected_folder="reloadFolder" />
            </div>
        </div>

        <!-- Children -->
        <TransitionGroup
            v-if="hasChildren && isExpanded"
            tag="div"
            class="ml-2 border-l border-base-300/40"
            enter-active-class="transition-all duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-0.5"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-0.5"
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
:root {
    /* Visual baseline for tree indentation */
    --tree-indent: 16px;
}

.tree-row {
    /* Indent using CSS var and the provided level */
    padding-left: calc(8px + (var(--level) * var(--tree-indent, 16px)));
}

/* Add a subtle connector elbow like Windows Explorer */
.tree-row::before {
    content: '';
    position: absolute;
    left: calc((var(--level) * var(--tree-indent, 16px)) + 4px);
    top: 50%;
    width: calc(var(--tree-indent, 16px) - 8px);
    height: 0;
    border-top: 1px solid hsl(var(--bc) / 0.25);
    transform: translateY(-50%);
    pointer-events: none;
}

/* Don’t draw elbow on root level */
.tree-row.is-root::before {
    display: none;
}

/* Tighten density a bit and smooth hover */
.tree-row:hover {
    background-image: linear-gradient(hsl(var(--b2) / 0.4), hsl(var(--b2) / 0.4));
}

/* Ensure truncation remains clean */
.tree-row .truncate {
    max-width: 100%;
}
</style>
