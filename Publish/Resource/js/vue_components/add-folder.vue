<template>
    <div class="relative py-3 px-1">
        <div @click="addModal()" >
            <a href="javascript:;" >
                <icon class="w-5 h-5" :name="'plus-circle'" > </icon>
            </a>
        </div>

        <!-- modal delete -->
        <div class="fixed z-10 inset-0 overflow-y-auto" v-if="enable" >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <!-- Heroicon name: outline/exclamation -->
                                <!-- <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg> -->

                                <icon :class="'h-6 w-6 text-green-600'" :name="'folder'" > </icon>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                                    Folder Name
                                </h3>
                                <div class="mt-2">
                                    <input class="shadow appearance-none border rounded py-2 px-3 text-grey-darker" v-model="folder" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="acceptRequest()"
                        >
                        Add
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="addModal()"
                        >
                        Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script>
    export default {
        name: "edit-assistant",
        props: {
            parent_id: {
                type: Number,
                default: null
            }
        },
        data: function() {
            return {
                enable: false,
                folder: '',
            };
        },
        methods: {
            addModal() {
                if (this.enable) {
                    this.enable = false;
                } else {
                    this.enable = true;
                }
            },
            acceptRequest() {
                this.addModal();
                axios.post('folder/create', {
                    name:this.folder,
                    parent_id: this.parent_id
                })
                .then(function (response) {

                })
                .catch(error => {
                  if (error.response) {
                        for (const [key, value] of Object.entries(error.response.data.errors)) {
                            this.unityToast(value, '#9b2020');
                        }
                    }
              });
              var item = {
                  id:this.parent_id
              };

                this.$emit('load_folder', item);
            }
        },
        created() {},
        computed: {},
        mounted() {

        }
    };
</script>
<style></style>

