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
                    v-bind:item="item"
                    @load_folder="reloadFolder"
                    @load_selected_folder="loadSelectedFolder"
                ></sidebar>
            </div>
            <!-- end right section -->
            <!-- left section -->
            <media-content v-bind:parent_id="folder_target" >
                <template #breadcrumb >
                    <breadcrumb
                        @load_root="loadParents"
                        @load_selected_folder="loadSelectedFolder"
                        v-bind:breadcrumb="breadcrumb"
                    >
                    </breadcrumb>
                </template>
                <template #created >
                    {{ folder_created_at }}
                </template>
            </media-content>
            <!-- end left section -->
        </div>
    </div>
</template>
<script>
    export default {
        name: "media-folder",
        props: {
            editroute: {
                type: String,
                default: ""
            }
        },
        data: function() {
            return {
                folders          : [],
                breadcrumb       : [],
                folder_target    : null,
                folder_created_at: '',
            };
        },
        methods: {
            reloadFolder(value) {
                if (value.id === null) {
                    this.loadParents();
                } else {
                    this.loadFolder(value.id);
                }
            },
            loadSelectedFolder (item) {
                this.loadFolder(item.id);
                this.folder_target = item.id;
            },
            loadParents() {
                axios.get('folder/list', {
                })
                .then(response => {
                    this.folders       = response.data.data;
                    this.breadcrumb    = [];
                    this.folder_target = null;
                })
                .catch(function (error) {
                })
            },
            loadFolder(id) {
                axios.get('/folder/load/'+id, {
                })
                .then(response => {
                    this.folders           = response.data.children;
                    this.breadcrumb        = response.data.parent;
                    this.folder_created_at = response.data.folder_info.created_at;
                })
                .catch(function (error) {
                });
            },

        },
        created() {},
        computed: {},
        mounted() {
            setTimeout(() => {
                this.loadParents();
            }, 1);
        }
    };
</script>
<style></style>

