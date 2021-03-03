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
        <!-- products -->
        <div class="grid grid-cols-3 gap-4 px-5 mt-5 overflow-y-auto h-3/4">
                <div
                    v-for="(item, index) in file" :key="index"
                    class="px-3 py-3 flex flex-col border border-gray-200 rounded-md h-32 justify-between">
                    <div>
                        <div class="font-bold text-gray-800">{{ item.name }}</div>
                        <span class="font-light text-sm text-gray-400">{{ item.ext }}</span>
                    </div>
                    <div class="flex flex-row justify-between items-center">
                        <span class="self-end font-bold text-lg text-yellow-500">
                            {{ item.media_size }}
                        </span>
                        <div v-if="extension.includes(item.ext)" >
                            <img :src="item.url[4]" class=" h-14 w-14 object-cover rounded-md" alt="">
                        </div>
                    </div>
                </div>
        </div>
        <!-- end products -->    </div>
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
          file: []
        };
      },
      methods: {
          showModal () {
              this.unityToast('Drag and drop your file');
          },
          addFile(e) {
                let droppedFiles = e.dataTransfer.files;
                if(!droppedFiles) return;
                // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
                for (const [key, value] of Object.entries(droppedFiles)) {
                    let formData = new FormData();
                    formData.append('file', value);
                    if (this.parent_id == null) {
                        this.unityToast('Select a folder');
                    } else {
                        axios.post('/file/upload/'+this.parent_id, formData, {
                            headers: {
                            'Content-Type': 'multipart/form-data'
                            }
                        });
                        this.loadFiles();
                    }
                }
            },
            loadFiles() {
                axios.get('/folder/files/' + this.parent_id, {
                })
                .then(response => {
                    this.file = response.data.data;
                })
                .catch(function (error) {
                });
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

