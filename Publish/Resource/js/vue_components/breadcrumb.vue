<template>
    <div>
        <nav class="text-black dark:text-white font-bold my-8" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center cursor-pointer" @click="loadParents()" >
                    <icon class="w-5 h-5" :name="'home'" > </icon>
                    <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/>
                    </svg>
                </li>

                <li v-for="(item, index) in breadcrumb" :key="index"
                    @click="loadFolder(item)"
                    class="flex items-center">
                    <a href="#">{{ item.name }}</a>
                    <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/>
                    </svg>
                </li>
            </ol>
        </nav>
    </div>
</template>
<script setup >

    // Props
    const props = defineProps({
        breadcrumb: {
          type: Object,
          default: {}
        }
    });

    const emit = defineEmits(['load_root','load_selected_folder']);
    let data = $ref([]);

    const loadParents = async () => {
        emit('load_root');
    }
    const loadFolder = async (item) => {
        emit('load_selected_folder', item);
    }

</script>
<style></style>

