<template>
    <div v-cloak @drop.prevent="drangAndDropFile" @dragover.prevent @dragenter="showModal" @dragleave="showModal"
        class="w-full min-h-screen bg-base-300">
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
                    <button class="btn btn-primary" @click="addFileModal">+</button>
                    <!-- modal upload -->
                    <div class="fixed z-10 inset-0 overflow-y-auto" v-if="add_modal_file_enable">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div
                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <!-- Heroicon name: outline/exclamation -->
                                            <!-- <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg> -->

                                            <icon :class="'h-6 w-6 text-green-600'" :name="'folder'">
                                            </icon>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                                                File Upload
                                            </h3>
                                            <div class="mt-2">
                                                <input type="file" multiple
                                                    class="shadow appearance-none border rounded py-2 px-3 text-black"
                                                    id="file" ref="file" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button class="btn btn-error" @click="addFileModal()">Cancel</button>
                                    <button class="btn btn-primary" @click="handleFileUpload">Upload</button>
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

        <div class="flow-root p-10">
            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                <li class="py-3 sm:py-4 border-b transition duration-300 ease-in-out hover:bg-base-200" v-for="(item, index) in file" :key="index">
                    <div class="flex items-center space-x-4">
                        <div class="avatar">
                            <div class="w-24 rounded">
                                <div v-if="extension.includes(item.ext)">
                                    <image-edit @loading="loading" @load_file="loadFiles" v-bind:item="item" />
                                </div>
                                <!-- not editable files -->
                                <div v-else>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                {{ item.name }}
                            </p>
                            <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                {{ item.ext }}
                            </p>
                        </div>
                        <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">

                            <div class="btn-group btn-group-vertical lg:btn-group-horizontal">
                                <a class="btn btn-active" target="_blank" :href="item.url['default']">Download</a>
                                <button class="btn">{{ item.media_size }}</button>
                                <button class="btn btn-error"><edit-assistant-media @load_folder="loadFiles" v-bind:item="item" /></button>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <!-- end media list -->

        <!-- Loading  -->
        <!-- v-if="is_loading" -->
        <div v-if="is_loading"
            class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-black opacity-75 flex flex-col items-center justify-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
            <h2 class="text-center text-white text-xl font-semibold">
                Loading...
            </h2>
            <p class="w-1/3 text-center text-white">
                This may take a few seconds, please don't close this page.
            </p>
        </div>
    </div>
</template>
<script setup>

// Import watch from vue
import { watch } from 'vue'
import { startWindToast } from "@mariojgt/wind-notify/packages/index.js";

// Props
const props = defineProps({
    parent_id: {
        type: Number,
        default: null,
    },
    extension: {
        type: Array,
        default: ["jpeg", "jpg", "png", "gif", "webp"],
    }
});

// Data
let file = $ref([]);
let add_modal_file_enable = $ref(false);
let is_loading = $ref(false);


const showModal = async (id, name) => {
    startWindToast('File', 'Drag and drop your file', 'info', 11, 'top');
};

const uploadFile = async (fileRef) => {
    let formData = new FormData();
    formData.append("file", fileRef);
    if (props.parent_id == null) {
        startWindToast('File', 'Select a folder', 'warning', 11, 'top');
    } else {
        await axios
            .post("/file/upload/" + props.parent_id, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            })
            .catch((error) => {
                if (error.response) {
                    for (const [key, value] of Object.entries(
                        error.response.data.errors
                    )) {
                        startWindToast('info', value, 'info', 11, 'top');
                    }
                }
            });
    }
};

const handleFileUpload = async () => {
    // Reference to the files
    loading();
    let files = document.getElementById("file").files;
    for (const [key, value] of Object.entries(files)) {
        await uploadFile(value);
    }
    loadFiles();
    loading();
    addFileModal();
};

const drangAndDropFile = async (e) => {
    let droppedFiles = e.dataTransfer.files;
    if (!droppedFiles) return;
    // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
    loading();
    for (const [key, value] of Object.entries(droppedFiles)) {
        await uploadFile(value);
    }
    loadFiles();
    loading();
};

const loadFiles = async (e) => {
    if (props.parent_id || props.parent_id === 0) {
        loading();
        await axios
            .get("/folder/files/" + props.parent_id, {})
            .then((response) => {
                file = response.data.data;
            })
            .catch(function (error) { });
        loading();
    }
};

const loading = async (e) => {
    if (is_loading) {
        is_loading = false;
    } else {
        is_loading = true;
    }
};

// Modal to that add files
const addFileModal = async () => {
    if (add_modal_file_enable) {
        add_modal_file_enable = false;
    } else {
        add_modal_file_enable = true;
    }
};

watch(
    () => props.parent_id,
    (val) => {
        if (val === null) {
            file = [];
        } else {
            loadFiles();
        }
    }
);

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
