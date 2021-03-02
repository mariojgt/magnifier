<template>
    <div class="mx-auto px-5 bg-white">
        <div class="flex lg:flex-row flex-col-reverse shadow-lg">
            <!-- right section -->
            <div class="w-full lg:w-auto">
                <!-- header -->
                <div class="flex flex-row items-center justify-between px-5 mt-5">
                    <div class="font-bold text-xl">
                        Media Folders
                        <add-folder @load_folder="reloadFolder"></add-folder>
                    </div>
                </div>
                <!-- end header -->
                <sidebar
                    v-for="(item, index) in folders" :key="index"
                    v-bind:item="item"
                    @load_folder="reloadFolder"
                ></sidebar>
            </div>
            <!-- end right section -->
            <!-- left section -->
            <div class="w-full min-h-screen shadow-lg">
                <!-- header -->
                <div class="flex flex-row justify-between items-center px-5 mt-5">
                    <div class="text-gray-800">
                        <div class="font-bold text-xl">Media</div>
                    </div>
                    <div class="flex items-center">
                        <div class="text-sm text-center mr-4">
                            <div class="font-light text-gray-500">last action</div>
                            <span class="font-semibold">3 mins ago</span>
                        </div>
                        <div>
                            <span
                                class="px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded"
                                >
                            Documentation
                            </span>
                        </div>
                    </div>
                </div>
                <!-- end header -->
                <!-- categories -->
                <div class="mt-5 flex flex-row px-5">
                    <span
                        class="px-5 py-1 bg-yellow-500 rounded-2xl text-white text-sm mr-4"
                        >
                    All items
                    </span>
                    <span class="px-5 py-1 rounded-2xl text-sm font-semibold mr-4">
                    Food
                    </span>
                    <span class="px-5 py-1 rounded-2xl text-sm font-semibold mr-4">
                    Cold Drinks
                    </span>
                    <span class="px-5 py-1 rounded-2xl text-sm font-semibold mr-4">
                    Hot Drinks
                    </span>
                </div>
                <!-- end categories -->
                <!-- products -->
                <div class="grid grid-cols-3 gap-4 px-5 mt-5 overflow-y-auto h-3/4">
                    <media-content> </media-content>
                </div>
                <!-- end products -->
            </div>
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
                folders: [],
            };
        },
        methods: {
            reloadFolder(value) {
                this.loadParents();
            },
            loadParents() {
                axios.get('folder/list', {
                })
                .then(response => {
                    this.folders = response.data.data;
                })
                .catch(function (error) {
                })
            }
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

