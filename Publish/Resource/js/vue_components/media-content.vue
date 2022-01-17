<template>
    <div
        v-cloak
        @drop.prevent="drangAndDropFile"
        @dragover.prevent
        @dragenter="showModal"
        @dragleave="showModal"
        class="w-full min-h-screen shadow-lg border-dotted border-4 bg-gray-800 dark:border-white border-black"
    >
        <!-- header -->
        <div class="flex flex-row justify-between items-center px-5 mt-5">
            <div class="text-gray-800">
                <div class="font-bold text-xl dark:text-white">Media</div>
                <slot name="breadcrumb"> </slot>
            </div>
            <div class="flex items-center text-black dark:text-white">
                <div class="text-sm text-center mr-4">
                    <div class="font-semibold">Created At</div>
                    <span class="font-semibold">
                        <slot name="created"> </slot>
                    </span>
                </div>
                <div>
                    <div @click="addFileModal()">
                        <span
                            class="px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded"
                        >
                            +
                        </span>
                    </div>

                    <!-- modal upload -->
                    <div
                        class="fixed z-10 inset-0 overflow-y-auto"
                        v-if="add_modal_file_enable"
                    >
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
                        >
                            <div
                                class="fixed inset-0 transition-opacity"
                                aria-hidden="true"
                            >
                                <div
                                    class="absolute inset-0 bg-gray-500 opacity-75"
                                ></div>
                            </div>

                            <span
                                class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true"
                                >&#8203;</span
                            >
                            <div
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="modal-headline"
                            >
                                <div
                                    class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4"
                                >
                                    <div class="sm:flex sm:items-start">
                                        <div
                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                                        >
                                            <!-- Heroicon name: outline/exclamation -->
                                            <!-- <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg> -->

                                            <icon
                                                :class="'h-6 w-6 text-green-600'"
                                                :name="'folder'"
                                            >
                                            </icon>
                                        </div>
                                        <div
                                            class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left"
                                        >
                                            <h3
                                                class="text-lg leading-6 font-medium text-gray-900"
                                                id="modal-headline"
                                            >
                                                File Upload
                                            </h3>
                                            <div class="mt-2">
                                                <input
                                                    type="file"
                                                    multiple
                                                    class="shadow appearance-none border rounded py-2 px-3 text-black"
                                                    id="file"
                                                    ref="file"
                                                    @change="handleFileUpload()"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
                                >
                                    <button
                                        type="button"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                        @click="addFileModal()"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End add file modal -->
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
                v-for="(item, index) in file"
                :key="index"
                class="px-3 py-3 flex flex-col border-4 border-black dark:border-white dark:text-white dark:hover:text-black dark:hover:border-black dark:hover:bg-white border-dashed rounded-md h-32 justify-between hover:bg-black hover:text-white hover:border-white transition duration-150"
            >
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
                        v-bind:item="item"
                    >
                    </edit-assistant-media>
                    <!-- If is image -->
                    <div v-if="extension.includes(item.ext)">
                        <image-edit
                            @loading="loading"
                            @load_file="loadFiles"
                            v-bind:item="item"
                        >
                        </image-edit>
                    </div>
                    <!-- not editable files -->
                    <div v-else>
                        <icon class="w-5 h-5" :name="'file'"> </icon>
                    </div>
                </div>
            </div>
        </div>
        <!-- end media list -->

        <!-- Loading  -->
        <!-- v-if="is_loading" -->
        <div
            v-if="is_loading"
            class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-black opacity-75 flex flex-col items-center justify-center"
        >
            <div
                class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"
            ></div>
            <h2 class="text-center text-white text-xl font-semibold">
                Loading...
            </h2>
            <p class="w-1/3 text-center text-white">
                This may take a few seconds, please don't close this page.
            </p>
        </div>
    </div>
</template>
<script>
export default {
    name: "media-content",
    props: {
        parent_id: {
            type: Number,
            default: null,
        },
        extension: {
            type: Array,
            default: ["jpeg", "jpg", "png", "gif", "webp"],
        },
    },
    data: function () {
        return {
            file: [],
            add_modal_file_enable: false,
            is_loading: false,
        };
    },
    methods: {
        showModal() {
            this.unityToast("Drag and drop your file");
        },
        async uploadFile(fileRef) {
            let formData = new FormData();
            formData.append("file", fileRef);
            if (this.parent_id == null) {
                this.unityToast("Select a folder");
            } else {
                await axios
                    .post("/file/upload/" + this.parent_id, formData, {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    })
                    .catch((error) => {
                        if (error.response) {
                            for (const [key, value] of Object.entries(
                                error.response.data.errors
                            )) {
                                this.unityToast(value);
                            }
                        }
                    });
            }
        },
        async handleFileUpload() {
            // Reference to the files
            this.loading();
            for (const [key, value] of Object.entries(this.$refs.file.files)) {
                await this.uploadFile(value);
            }
            this.loadFiles();
            this.loading();
            addFileModal();
        },
        async drangAndDropFile(e) {
            let droppedFiles = e.dataTransfer.files;
            if (!droppedFiles) return;
            // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
            this.loading();
            for (const [key, value] of Object.entries(droppedFiles)) {
                await this.uploadFile(value);
            }
            this.loadFiles();
            this.loading();
        },
        async loadFiles() {
            if (this.parent_id || this.parent_id === 0) {
                this.loading();
                await axios
                    .get("/folder/files/" + this.parent_id, {})
                    .then((response) => {
                        this.file = response.data.data;
                    })
                    .catch(function (error) {});
                this.loading();
            }
        },
        loading() {
            if (this.is_loading) {
                this.is_loading = false;
            } else {
                this.is_loading = true;
            }
        },
        // Modal to that add files
        addFileModal() {
            if (this.add_modal_file_enable) {
                this.add_modal_file_enable = false;
            } else {
                this.add_modal_file_enable = true;
            }
        },
    },
    watch: {
        // On prop change we load the files lis
        parent_id: function (val) {
            if (val === null) {
                this.file = [];
            } else {
                this.loadFiles();
            }
        },
    },
    created() {},
    computed: {},
    mounted() {},
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
