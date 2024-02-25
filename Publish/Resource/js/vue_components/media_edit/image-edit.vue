<template>
    <div class="cursor-pointer">
        <img :src="props.item.url['default']" @click="expandImageLoad(item)" alt="" />

        <!-- Put this part before </body> tag -->
        <Teleport to="body">
            <input type="checkbox" :id="('media-edit-' + props.item)" class="modal-toggle" :checked="expand_image" />
            <div class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold">Edit Media Tags</h3>
                    <figure class="px-10 pt-10 pb-10">
                        <img :src="props.item.url['default']" alt="Shoes" class="rounded-xl" />
                    </figure>
                    <div class="flex flex-col gap-4 w-full items-center">
                        <input type="text" v-model="title" placeholder="title"
                            class="input input-bordered input-primary w-full max-w-xs" />
                        <input type="text" v-model="alt" placeholder="alt"
                            class="input input-bordered input-primary w-full max-w-xs" />
                        <input type="text" v-model="caption" placeholder="caption"
                            class="input input-bordered input-primary w-full max-w-xs" />
                        <input type="text" v-model="description" placeholder="description"
                            class="input input-bordered input-primary w-full max-w-xs" />
                    </div>
                    <div class="modal-action">
                        <label @click="expandImage" class="btn">Close</label>
                        <label @click="updateDetails(props.item.id)" class="btn">Update</label>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Expand image panel -->

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

const emit = defineEmits(['loading', 'load_file']);

let expand_image = $ref(false);
let title = $ref(null);
let alt = $ref(null);
let caption = $ref(null);
let description = $ref(null);

const toogleWishList = async (id, name) => {
    addToCart({
        product_id: id,
        name: name,
    });
};

let expandImage = async () => {
    if (expand_image) {
        expand_image = false;
    } else {
        expand_image = true;
    }
};
let expandImageLoad = async () => {
    expandImage();
    // Load the bind values for the edit fildes
    //console.log(file.title);
    title = props.item.title;
    alt = props.item.alt;
    caption = props.item.caption;
    description = props.item.description;
};
let updateDetails = async (id) => {
    emit("loading");
    await axios
        .post("/file/update/" + id, {
            title: title,
            alt: alt,
            caption: caption,
            description: description,
        })
        .then(function (response) { })
        .catch(function (error) { });
    emit("loading");
    emit("load_file");
    //loadFiles();
    //loading();
};
</script>
<style>

</style>
