<template>
    <div
            v-cloak @drop.prevent="addFile" @dragover.prevent
            @dragenter="showModal"
            @dragleave="showModal"
     class="w-full min-h-screen shadow-lg border-dotted border-4 bg-gray-800 dark:border-white border-black">
        <!-- header -->
        <div class="flex flex-row justify-between items-center px-5 mt-5">
            <div class="text-gray-800">
                <div class="font-bold text-xl dark:text-white">Media</div>
                <slot name="breadcrumb" >
                </slot>
            </div>
            <div class="flex items-center">
                <div class="text-sm text-center mr-4">
                    <div class="font-semibold text-black dark:text-white">Created At</div>
                    <span class="font-semibold">
                        <slot name="created" >
                        </slot>
                    </span>
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
        <!-- <div class="mt-5 flex flex-row px-5">
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
        </div> -->
        <!-- end categories -->
        <!-- Media list -->
        <div class="grid grid-cols-3 gap-4 px-5 mt-5 overflow-y-auto h-3/4">
            <div
                v-for="(item, index) in file" :key="index"
                class="px-3 py-3 flex flex-col border-4 border-black
                dark:border-white dark:text-white dark:hover:text-black dark:hover:border-black dark:hover:bg-white border-dashed rounded-md h-32 justify-between hover:bg-black hover:text-white hover:border-white transition duration-150">
                <div>
                    <div class="font-bold">
                        {{ item.name }}
                    </div>
                    <span class="font-extrabold text-sm">
                        <strong>
                            {{ item.ext }}
                        </strong>
                    </span>
                </div>
                <div class="flex flex-row justify-between items-center">
                    <span class="self-end font-bold text-lg">
                        {{ item.media_size }}
                    </span>
                    <edit-assistant-media
                        @load_folder="loadFiles"
                        v-bind:item="item" >
                    </edit-assistant-media>
                    <!-- If is image -->
                    <div v-if="extension.includes(item.ext)" >
                        <image-edit
                        @loading="loading"
                        @load_file="loadFiles"
                        v-bind:item="item" >
                        </image-edit>
                    </div>
                    <!-- not editable files -->
                    <div v-else >
                        <icon class="w-5 h-5" :name="'file'" > </icon>
                    </div>
                </div>
            </div>
        </div>
        <!-- end media list -->

        <!-- Loading  -->
        <!-- v-if="is_loading" -->
        <div v-if="is_loading" class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-black opacity-75 flex flex-col items-center justify-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4">
            </div>
            <h2 class="text-center text-white text-xl font-semibold">Loading...</h2>
            <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
        </div>
    </div>
</template>
<script>
    export default {
        name: "media-content",
        props: {
            parent_id: {
                type: Number,
                default: null
            },
            extension: {
                type: Array,
                default: ['jpeg', 'jpg', 'png', 'gif', 'webp']
            }
        },
        data: function() {
            return {
                file: [],
                is_loading: false,
            };
        },
        methods: {
            showModal() {
                this.unityToast('Drag and drop your file');
            },
            async addFile(e) {
                let droppedFiles = e.dataTransfer.files;
                if (!droppedFiles) return;
                // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
                this.loading();
                for (const [key, value] of Object.entries(droppedFiles)) {
                    let formData = new FormData();
                    formData.append('file', value);
                    if (this.parent_id == null) {
                        this.unityToast('Select a folder');
                    } else {
                        const results = await axios.post('/file/upload/' + this.parent_id, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                    }
                }
                this.loadFiles();
                this.loading();
            },
            async loadFiles() {
                if (this.parent_id || this.parent_id === 0) {
                    this.loading();
                    await axios.get('/folder/files/' + this.parent_id, {})
                        .then(response => {
                            this.file = response.data.data;
                        })
                        .catch(function(error) {});
                    this.loading();
                }
            },
            loading() {
                if (this.is_loading) {
                    this.is_loading = false
                } else {
                    this.is_loading = true;
                }
            },
        },
        watch: {
            // On prop change we load the files lis
            parent_id: function(val) {
                if (val === null) {
                    this.file = [];
                } else {
                    this.loadFiles();
                }
            }
        },
        created() {},
        computed: {},
        mounted() {

        }
    };
</script>
<style>

.loader {
	border-top-color: #000;
	-webkit-animation: spinner 1.5s linear infinite;
	animation: spinner 1.5s linear infinite;
}

@-webkit-keyframes spinner {
	0% {
		-webkit-transform: rotate(0deg);
	}
	100% {
		-webkit-transform: rotate(360deg);
	}
}

@keyframes spinner {
	0% {
		transform: rotate(0deg);
	}
	100% {
		transform: rotate(360deg);
	}
}

</style>

