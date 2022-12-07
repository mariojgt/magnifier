<template>
    <div class="dropdown dropdown-right">
        <div tabindex="0" class="m-1 btn btn-circle modal-button">
            <icon :name="'edit'" />
        </div>
        <ul tabindex="0" class="
                    p-2
                    shadow
                    menu
                    dropdown-content
                    bg-base-100
                    rounded-box
                    w-52
                    text-center
                ">
            <li>
                <a @click="renameModal()" >Rename</a>
            </li>
            <li>
                <a @click="deleteModal()" >Delete</a>
            </li>
        </ul>
    </div>

    <!-- Delete modal -->
    <Teleport to="body">
        <input type="checkbox" :id="('delete-media-modal' + randomId)" :checked="delete_modal" class="modal-toggle" />
        <div class="modal">
            <div class="modal-box relative">
                <button class="btn btn-sm btn-circle absolute right-2 top-2" @click="deleteModal">x</button>
                <h3 class="text-lg font-bold">Delete Folder</h3>
                <p class="py-4">Delete a folder will delete all the
                    subfolder and files this action is not
                    reversible.</p>
                <div class="modal-action">
                    <button class="btn btn-success" @click="deleteModal">Cancel</button>
                    <button class="btn btn-danger" @click="acceptRequest">Delete</button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- modal edit -->
    <Teleport to="body">
        <input type="checkbox" :id="('rename-folder' + randomId)" :checked="rename_modal" class="modal-toggle" />
        <div class="modal">
            <div class="modal-box relative">
                <button class="btn btn-sm btn-circle absolute right-2 top-2" @click="renameModal">x</button>
                <h3 class="text-lg font-bold">Rename folder</h3>
                <input type="text" v-model="folder" placeholder="Folder Name" class="input input-bordered input-primary w-full mt-5" />
                <div class="modal-action">
                    <button class="btn btn-danger" @click="renameModal">Cancel</button>
                    <button class="btn btn-success" @click="acceptRenameRequest">Rename</button>
                </div>
            </div>
        </div>
    </Teleport>

</template>
<script setup >

// Props
const props = defineProps({
    item: {
        type: Object,
        default: []
    }
});

let enable = $ref(false);
let delete_modal = $ref(false);
let rename_modal = $ref(false);
let folder = $ref("");

let randomId = (Math.random() + 1).toString(36).substring(7);

const emit = defineEmits(["load_selected_folder"]);

const enableHelper = async () => {
    if (enable) {
        enable = false;
    } else {
        enable = true;
    }
};

const deleteModal = async () => {
    if (delete_modal) {
        delete_modal = false;
    } else {
        delete_modal = true;
    }
};

const renameModal = async () => {
    folder = props.item.name;
    if (rename_modal) {
        rename_modal = false;
    } else {
        rename_modal = true;
    }
};

const acceptRenameRequest = async () => {
    axios
        .post("/folder/rename/" + props.item.id, {
            new_name: folder,
        })
        .then(function (response) { })
        .catch(function (error) { });

    var item = {
        id: props.item.parent_id,
    };
    emit("load_selected_folder", props.item);
    renameModal();
    enableHelper();
};

const acceptRequest = async () => {
    await axios
        .delete("folder/delete/" + props.item.id, {})
        .then((response) => {
            //console.log(response);
        })
        .catch(function (error) { });
    var item = {
        id: props.item.parent_id,
    };
    emit("load_selected_folder", null);
    deleteModal();
    enableHelper();
};


</script>
<style>

</style>
