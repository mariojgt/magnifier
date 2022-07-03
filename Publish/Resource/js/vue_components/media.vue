<template>
    <div class="mx-auto px-5 bg-white dark:bg-black">
        <div class="flex lg:flex-row flex-col-reverse shadow-lg">
            <!-- right section -->
            <div class="w-full lg:w-auto">
                <!-- header -->
                <div class="flex dark:text-white flex-row items-center justify-between px-5 mt-5">
                    <div class="font-bold text-xl">
                        Media Folders
                        <add-folder
                            v-bind:parent_id="folder_target"
                            @load_folder="reloadFolder"
                        ></add-folder>
                    </div>
                </div>

                <!-- end header -->
                <sidebar
                    v-for="(item, index) in folders" :key="index"
                    :item="item"
                    @load_folder="reloadFolder"
                    @load_selected_folder="loadSelectedFolder"
                ></sidebar>
            </div>
            <!-- end right section -->
            <!-- left section -->
            <media-content :parent_id="folder_target" >
                <template #breadcrumb >
                    <pathmaker
                        @load_root="loadParents"
                        @load_selected_folder="loadSelectedFolder"
                        :breadcrumb="breadcrumb"
                    >
                    </pathmaker>
                </template>
                <template #created >
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

    let folders           = $ref([]);
    let breadcrumb        = $ref([]);
    let folder_target     = $ref(null);
    let folder_created_at = $ref('');

    const reloadFolder = async (value) => {
        if (value.id === null) {
            loadParents();
        } else {
            loadFolder(value.id);
        }
    }
    const loadSelectedFolder = async  (item) => {
        loadFolder(item.id);
        folder_target = item.id;
    }
    const loadParents = async () => {
        axios.get('folder/list', {
        })
        .then(response => {
            folders       = response.data.data;
            breadcrumb    = [];
            folder_target = null;
        })
        .catch(function (error) {
        })
    }
    const loadFolder = async (id) => {
        axios.get('/folder/load/'+id, {
        })
        .then(response => {
            folders           = response.data.children;
            breadcrumb        = response.data.parent;
            folder_created_at = response.data.folder_info.created_at;
        })
        .catch(function (error) {
        });
    }

     setTimeout(() => {
        loadParents();
    }, 1);
</script>
<style></style>

