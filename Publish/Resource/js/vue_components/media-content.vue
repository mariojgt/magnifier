<template>
    <div
            v-cloak @drop.prevent="addFile" @dragover.prevent
            @dragenter="showModal"
            @dragleave="showModal"
     class="w-full min-h-screen shadow-lg">
        <!-- header -->
        <div class="flex flex-row justify-between items-center px-5 mt-5">
            <div class="text-gray-800">
                <div class="font-bold text-xl">Media</div>
                <slot name="breadcrumb" >
                </slot>
            </div>
            <div class="flex items-center">
                <div class="text-sm text-center mr-4">
                    <div class="font-light text-gray-500">Created At</div>
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
                class="px-3 py-3 flex flex-col border border-gray-200 rounded-md h-32 justify-between">
                <div>
                    <div class="font-bold text-gray-800">
                        {{ item.name }}
                    </div>
                    <span class="font-light text-sm text-gray-400">{{ item.ext }}</span>
                </div>
                <div class="flex flex-row justify-between items-center">
                    <span class="self-end font-bold text-lg text-yellow-500">
                        {{ item.media_size }}
                    </span>
                    <edit-assistant-media
                        @load_folder="loadFiles"
                        v-bind:item="item" >
                    </edit-assistant-media>
                    <div v-if="extension.includes(item.ext)" >
                        <img :src="item.url[4]" class=" h-14 w-14 object-cover rounded-md" @click="expandImageLoad(item)" alt="">
                    </div>
                </div>
            </div>
        </div>
        <!-- end media list -->

        <!-- Expand image panel -->
        <div class="fixed z-10 inset-0 overflow-y-auto" v-if="expand_image">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="w-full h-full fixed block top-0 left-0 bg-white opacity-75 z-50">
                    <div class="flex flex-col justify-center items-center max-w-sm mx-auto my-8">
                        <div
                            v-bind:style="{ 'background-image': 'url(' + this.selected_file.url[0] + ')' }"
                            class="bg-gray-300 h-96 w-full rounded-lg shadow-md bg-cover bg-center"></div>
                        <div class="w-56 md:w-64 bg-white -mt-10 shadow-lg rounded-lg overflow-hidden">
                            <div class="py-2 text-center font-bold uppercase tracking-wide text-gray-800">
                                <a :href="this.selected_file.url[0]" target="_blank" >
                                    {{ this.selected_file.name }}
                                </a>
                            </div>
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-400">
                                <h1 class="text-gray-800 font-bold ">
                                    {{ this.selected_file.created_at }}
                                </h1>
                                <button class=" bg-gray-800 text-xs text-white px-2 py-1 font-semibold rounded uppercase hover:bg-gray-700" @click="expandImage" >Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading  -->
        <div class="fixed z-10 inset-0 overflow-y-auto" v-if="is_loading" >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="w-full h-full fixed block top-0 left-0 bg-white opacity-75 z-50">
                        <span class="text-green-500 opacity-75 top-1/2 my-0 mx-auto block relative w-0 h-0" style="
                            top: 50%;
                            ">
                        <i class="fas fa-circle-notch fa-spin fa-5x"></i>
                        </span>
                    </div>
                </div>
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
          file         : [],
          selected_file: [],
          is_loading   : false,
          expand_image : false,
        };
      },
      methods: {
          expandImageLoad (file) {
              this.expandImage();
              this.selected_file = file;
              console.log(file);
          },
          showModal () {
              this.unityToast('Drag and drop your file');
          },
          async addFile(e) {
                let droppedFiles = e.dataTransfer.files;
                if(!droppedFiles) return;
                // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
                this.loading();
                for (const [key, value] of Object.entries(droppedFiles)) {
                    let formData = new FormData();
                    formData.append('file', value);
                    if (this.parent_id == null) {
                        this.unityToast('Select a folder');
                    } else {
                        const results = await axios.post('/file/upload/'+this.parent_id, formData,{
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
                        await axios.get('/folder/files/' + this.parent_id, {
                        })
                        .then(response => {
                            this.file = response.data.data;
                        })
                        .catch(function (error) {
                        });
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
            expandImage() {
                if (this.expand_image) {
                    this.expand_image = false
                } else {
                    this.expand_image = true;
                }
            }
      },
      watch: {
            // On prop change we load the files lis
            parent_id: function (val) {
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
<style></style>

