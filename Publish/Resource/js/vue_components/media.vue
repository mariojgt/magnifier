<template>
    <div class="bg-base-100">
        <div class="flex lg:flex-row flex-col-reverse shadow-lg">
            <!-- right section -->
            <div class="w-full lg:w-auto">
                <div
                    class="z-20 bg-base-200 bg-opacity-90 backdrop-blur sticky top-0 items-center gap-2 px-4 py-2 flex ">
                    <a href="#" @click="loadParents" class="flex-0 btn btn-ghost px-2">
                        <div
                            class="font-title text-primary inline-flex text-lg transition-all duration-200 md:text-3xl">
                            <span class="lowercase">Magnifier</span>
                        </div>
                    </a>
                    <div class="link link-hover font-mono text-xs text-opacity-50">
                        <add-folder v-bind:parent_id="folder_target" @load_folder="reloadFolder"></add-folder>
                    </div>
                </div>

                <!-- end header -->
                <sidebar v-for="(item, index) in folders" :key="index" :item="item" @load_folder="reloadFolder"
                    @load_selected_folder="reloadFolder"></sidebar>
            </div>
            <!-- end right section -->
            <!-- left section -->
            <media-content :parent_id="folder_target">
                <template #breadcrumb>
                    <pathmaker @load_root="loadParents" @load_selected_folder="loadSelectedFolder"
                        :breadcrumb="breadcrumb">
                    </pathmaker>
                </template>
                <template #created>
                    {{ folder_created_at }}
                </template>
            </media-content>
            <!-- end left section -->
        </div>
    </div>
</template>
<script setup >

// Props
const props = defineProps({
    editroute: {
        type: String,
        default: ""
    }
});

let folders = $ref([]);
let breadcrumb = $ref([]);
let folder_target = $ref(null);
let folder_created_at = $ref('');

const reloadFolder = async (value) => {
    if (!value?.id) {
        loadParents();
    } else {
        loadFolder(value.id);
    }
};
const loadSelectedFolder = async (item) => {
    loadFolder(item.id);
    folder_target = item.id;
};
const loadParents = async () => {
    axios.get('folder/list', {
    })
        .then(response => {
            folders = response.data.data;
            breadcrumb = [];
            folder_target = null;
        })
        .catch(function (error) {
        })
};
const loadFolder = async (id) => {
    axios.get('/folder/load/' + id, {
    })
        .then(response => {
            folders = response.data.children;
            breadcrumb = response.data.parent;
            folder_created_at = response.data.folder_info.created_at;
        })
        .catch(function (error) {
        });
};

setTimeout(() => {
    loadParents();
}, 1);
</script>
<style>
</style>

