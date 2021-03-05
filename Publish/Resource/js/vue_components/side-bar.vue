<template>
    <div class="cursor-pointer w-full p-1 rounded-t border-b
        dark:text-white dark:hover:bg-white dark:hover:text-black
        hover:bg-black hover:text-white ">
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
                        {{ item.created_at }}
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

