<template>
    <div>
        <div class="px-3 py-3 flex flex-col border border-gray-200 rounded-md h-32 justify-between">
            <div>
                <i class="h-14 w-14" data-feather="folder"></i>
                <span class="font-light text-sm text-gray-400">Extention</span>
            </div>
            <div class="flex flex-row justify-between items-center">
                <div class="font-bold text-gray-800">
                    Folder Name
                </div>
                <edit-assistant> </edit-assistant>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
      name: "example",
      props: {
        editroute: {
          type: String,
          default: ""
        }
      },
      data: function() {
        return {
          data: []
        };
      },
      methods: {},
      created() {},
      computed: {},
      mounted() {

      }
    };
</script>
<style></style>

