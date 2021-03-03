<template>
    <div>
        <!-- <div class="px-20 py-6 mt-6">
            <div class="w-full">
                <div class="flex cursor-pointer my-1 hover:bg-blue-lightest rounded">
                    <div class="w-8 h-10 text-center py-1">
                        <p class="text-3xl p-0 text-green-dark">
                            <icon :name="'folder'" > </icon>
                        </p>
                    </div>
                    <div class="w-4/5 h-10 py-3 px-1">
                        <p class="hover:text-blue-dark">{{ item.name }}</p>
                    </div>
                    <div class="w-1/5 h-10 text-right p-3">
                        <edit-assistant> </edit-assistant>
                    </div>
                    <div class="w-1/5 h-10 text-right p-3">
                        <edit-assistant> </edit-assistant>
                        <add-folder
                            @load_folder="reloadFolder"
                            v-bind:parent_id="this.item.id"
                        > </add-folder>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="cursor-pointer w-full border-gray-100 rounded-t border-b hover:bg-teal-100">
            <div class="flex w-full items-center p-2 pl-2 border-transparent border-l-2 relative hover:border-teal-100">
                <div class="w-6 flex flex-col items-center">
                    <div class="flex relative w-5 h-5 bg-orange-500 justify-center items-center m-1 mr-2 w-4 h-4 mt-1 rounded-full ">
                        <div v-if="item.count >= 1" >
                            <icon :name="'folder-plus'" > </icon>
                        </div>
                        <div v-else >
                            <icon :name="'folder'" > </icon>
                        </div>
                    </div>
                </div>
                <div class="w-full items-center flex" @click="loadRequest" >
                    <div class="mx-2 -mt-1  ">{{ item.name }}
                        <div class="text-xs truncate w-full normal-case font-normal -mt-1 text-gray-500">
                            soemthing here
                        </div>
                    </div>
                </div>
                <div class="w-1/5 h-10 text-right p-3">
                    <add-folder
                        @load_folder="reloadFolder"
                        v-bind:parent_id="this.item.id"
                    > </add-folder>
                </div>
                <div class="w-1/5 h-10 text-right p-3">
                    <edit-assistant
                    @load_selected_folder="reloadFolder"
                    v-bind:item="item"
                    > </edit-assistant>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
      name: "example",
      props: {
        item: Object,
      },
      data: function() {
        return {
          data: []
        };
      },
      methods: {
          reloadFolder (value) {
            this.$emit('load_folder', value);
          },
          loadRequest () {
                this.$emit('load_selected_folder', this.item);
          }
      },
      created() {},
      computed: {},
      mounted() {

      }
    };
</script>
<style></style>

