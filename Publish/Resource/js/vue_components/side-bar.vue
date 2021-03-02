<template>
    <div>
        <div class="px-20 py-6 mt-6">
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
                        <add-folder
                            @load_folder="reloadFolder"
                            v-bind:parent_id="this.item.id"
                        > </add-folder>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
      name: "example",
      props: {
        item: [],
      },
      data: function() {
        return {
          data: []
        };
      },
      methods: {
          reloadFolder (value) {
                this.$emit('load_folder', 'mario rocks');
          }
      },
      created() {},
      computed: {},
      mounted() {

      }
    };
</script>
<style></style>

