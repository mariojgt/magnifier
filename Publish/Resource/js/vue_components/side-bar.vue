<template>
    <div class="footer items-center p-4 bg-dark hover:bg-primary text-neutral-content rounded-md">
        <div class="items-center grid-flow-col cursor-pointer" @click="loadRequest">
            <div v-if="item.count >= 1">
                <icon :name="'folder-plus'"> </icon>
            </div>
            <div v-else>
                <icon :name="'folder'"> </icon>
            </div>
            <div class="mx-2 -mt-1  ">{{ item.name }}
                <div class="text-xs truncate w-full normal-case font-normal -mt-1">
                    {{ item.created_at }}
                </div>
            </div>
        </div>
        <div class="grid-flow-col gap-4 md:place-self-center md:justify-self-end">
            <add-folder @load_folder="reloadFolder" v-bind:parent_id="item.id"/>
            <edit-assistant @load_selected_folder="reloadFolder" v-bind:item="item"/>
        </div>
    </div>
</template>
<script setup >

// Props
const props = defineProps({
    item: {
        type: Object,
        default: []
    }
});

// Create a event that we goin to trigger on the store
const emit = defineEmits(["load_folder", "load_selected_folder"]);

const reloadFolder = async (value) => {
    emit('load_folder', value);
};

const loadRequest = async () => {
    emit('load_selected_folder', props.item);
};
</script>
<style>
</style>

