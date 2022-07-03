<template>
    <div>
        <!-- The button to open modal -->
        <label for="my-modal-add-folder" class="btn btn-circle modal-button">
            <icon :name="'plus-circle'"> </icon>
        </label>

        <!-- Put this part before </body> tag -->
        <input type="checkbox" id="my-modal-add-folder" class="modal-toggle" />
        <div class="modal">
            <div class="modal-box">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Folder Name</span>
                    </label>
                    <input type="text" placeholder="username" v-model="folder"
                        class="input input-primary input-bordered" />
                </div>
                <div class="modal-action">
                    <label for="my-modal-add-folder" class="btn btn-primary" @click="acceptRequest()">Create</label>
                    <label for="my-modal-add-folder" class="btn">Close</label>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup >

// Props
const props = defineProps({
    parent_id: {
        type: Number,
        default: null,
    }
});

let enable = $ref(false);
let folder = $ref("");

const emit = defineEmits(['load_folder']);

const acceptRequest = async () =>{
    axios
        .post("folder/create", {
            name: folder,
            parent_id: props.parent_id,
        })
        .then(function (response) { })
        .catch((error) => {
            if (error.response) {
                for (const [key, value] of Object.entries(
                    error.response.data.errors
                )) {
                    unityToast(value, "#9b2020");
                }
            }
        });
    // Prepare the item
    var item = {
        id: props.parent_id,
    };

    // Emit the event to load the other folder
    emit("load_folder", item);

    folder = null;
};

</script>
<style>
</style>
