<template>
    <div class="relative py-3 px-1">
        <div @click="enableHelper()" >
            <a href="javascript:;" >
                <span class="w-5 h-5" >
                    <icon :name="'edit'" > </icon>
                </span>
            </a>
        </div>
        <div class="options absolute bg-white text-gray-600 origin-top-right right-0 mt-2 w-56 rounded-md shadow-lg overflow-hidden z-0 md:z-50" v-if="enable">
            <a href="javascript:;" class="flex py-3 px-2 text-sm font-bold hover:bg-gray-100"
            @click="renameModal()"
            >
                <span class="mr-auto">Rename</span>
                <i class="w-5 h-5" data-feather="tag"></i>
            </a>
            <a href="javascript:;" class="flex py-3 px-2 text-sm font-bold bg-black text-white hover:bg-red-400" @click="deleteModal()" >
                <span class="mr-auto">Delete</span>
                <i class="w-5 h-5" data-feather="trash"></i>
            </a>
        </div>

        <!-- modal delete -->
        <div class="fixed z-10 inset-0 overflow-y-auto" v-if="delete_modal" >
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
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                                    Delete Folder
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Delete a folder will delete all the subfolder and files this action is not reversible.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="acceptRequest()"
                        >
                        Delete
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="deleteModal()"
                        >
                        Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal edit -->
        <div class="fixed z-10 inset-0 overflow-y-auto" v-if="rename_modal" >
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
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                                    Rename
                                </h3>
                                <div class="mt-2">
                                        <input class="w-full px-5  py-4 text-gray-700 bg-gray-200 rounded" v-model="folder" placeholder="Folder Name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="acceptRenameRequest()"
                        >
                        Rename
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="renameModal()"
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
            item: {
                type: Object,
                default: {}
            }
        },
        data: function() {
            return {
                enable      : false,
                delete_modal: false,
                rename_modal: false,
                folder      : ''
            };
        },
        methods: {
            enableHelper() {
                if (this.enable) {
                    this.enable = false;
                } else {
                    this.enable = true;
                }
            },
            deleteModal() {
                if (this.delete_modal) {
                    this.delete_modal = false;
                } else {
                    this.delete_modal = true;
                }
            },
            renameModal() {
                this.folder = this.item.name;
                if (this.rename_modal) {
                    this.rename_modal = false;
                } else {
                    this.rename_modal = true;
                }
            },
            acceptRenameRequest() {
                axios.post('/folder/rename/'+this.item.id, {
                    new_name:this.folder
                })
                .then(function (response) {
                })
                .catch(function (error) {
                });

                var item = {
                    id:this.item.parent_id
                };
                this.$emit('load_selected_folder', item);
                this.renameModal();
                this.enableHelper();
            },
           async acceptRequest() {
               await axios.delete('folder/delete/'+this.item.id, {
                })
                .then(response => {
                    //console.log(response);
                })
                .catch(function (error) {
                });
                var item = {
                    id:this.item.parent_id
                };
                this.$emit('load_selected_folder', item);
                this.deleteModal();
                this.enableHelper();
            }
        },
        created() {},
        computed: {},
        mounted() {
        }
    };
</script>
<style></style>

