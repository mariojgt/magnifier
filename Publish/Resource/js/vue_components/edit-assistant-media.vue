<template>
    <div class="relative py-3 px-1">
        <div @click="deleteModal">
            <a href="javascript:;">
                <span class="w-5 h-5">
                    <icon :name="'trash-2'"> </icon>
                </span>
            </a>
        </div>
        <!-- Delete modal -->
        <Teleport to="body">
            <input type="checkbox" id="delete-media-modal" :checked="delete_modal" class="modal-toggle" />
            <div class="modal">
                <div class="modal-box relative">
                    <button class="btn btn-sm btn-circle absolute right-2 top-2" @click="deleteModal">x</button>
                    <h3 class="text-lg font-bold">Delete Media</h3>
                    <p class="py-4">This action cannot be undone.</p>
                    <div class="modal-action">
                        <button class="btn btn-success" @click="deleteModal" >Cancel</button>
                        <button class="btn btn-error" @click="confirmDelete" >Delete</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
<script setup >

// Props
const props = defineProps({
    item: {
        type: Object,
        default: {}
    }
});

let delete_modal = $ref(false);

const emit = defineEmits(['load_folder']);

const deleteModal = async () => {
    if (delete_modal) {
        delete_modal = false;
    } else {
        delete_modal = true;
    }
};
const confirmDelete = async () => {
    const results = await axios.delete('/file/delete/' + props.item.id, {
    });
    // Emit the load folder
    emit('load_folder');
    deleteModal();
};
</script>
<style>

</style>

