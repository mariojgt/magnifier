<template>
    <div class="cursor-pointer" >
        <img
            :src="item.url['default']"
            @click="expandImageLoad(item)"
            alt=""
        />

        <!-- Expand image panel -->
        <div class="fixed z-10 inset-0 overflow-y-auto" v-if="expand_image">
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 transition-opacity"
                    aria-hidden="true"
                >
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >
                <div
                    class="w-full h-full fixed block top-0 left-0 bg-white opacity-75 z-50"
                >
                    <div
                        class="flex flex-col justify-center items-center max-w-sm mx-auto my-8"
                    >
                        <div
                            v-bind:style="{
                                'background-image':
                                    'url(' + this.item.url['default'] + ')',
                            }"
                            class="bg-gray-300 h-96 w-full rounded-lg shadow-md bg-cover bg-center"
                        ></div>
                        <div
                            class="w-56 md:w-96 bg-white -mt-10 shadow-lg rounded-lg overflow-hidden"
                        >
                            <div
                                class="py-2 text-center font-bold uppercase tracking-wide text-gray-800"
                            >
                                <a :href="this.item.url['default']" target="_blank">
                                    {{ this.item.name }}
                                </a>
                            </div>
                            <div
                                class="py-2 text-center font-bold uppercase tracking-wide text-gray-800"
                            >
                                <div class="flex">
                                    <span
                                        class="text-sm border border-2 rounded-l px-4 py-2 bg-gray-300 whitespace-no-wrap"
                                        >Title:</span
                                    >
                                    <input
                                        name="field_name"
                                        class="border border-2 rounded-r px-4 py-2 w-full"
                                        type="text"
                                        v-model="title"
                                        placeholder="Write something here..."
                                    />
                                </div>
                                <div class="flex">
                                    <span
                                        class="text-sm border border-2 rounded-l px-4 py-2 bg-gray-300 whitespace-no-wrap"
                                        >Alt:</span
                                    >
                                    <input
                                        name="field_name"
                                        class="border border-2 rounded-r px-4 py-2 w-full"
                                        type="text"
                                        v-model="alt"
                                        placeholder="Write something here..."
                                    />
                                </div>
                                <div class="flex">
                                    <span
                                        class="text-sm border border-2 rounded-l px-4 py-2 bg-gray-300 whitespace-no-wrap"
                                        >Caption:</span
                                    >
                                    <input
                                        name="field_name"
                                        class="border border-2 rounded-r px-4 py-2 w-full"
                                        type="text"
                                        v-model="caption"
                                        placeholder="Write something here..."
                                    />
                                </div>
                                <div class="flex">
                                    <span
                                        class="text-sm border border-2 rounded-l px-4 py-2 bg-gray-300 whitespace-no-wrap"
                                        >Description:</span
                                    >
                                    <input
                                        name="field_name"
                                        v-model="description"
                                        class="border border-2 rounded-r px-4 py-2 w-full"
                                        type="text"
                                        placeholder="Write something here..."
                                    />
                                </div>
                                <button
                                    class="button text-white bg-black shadow-md mr-2 w-full zoom-in"
                                    @click="updateDetails(this.item.id)"
                                >
                                    Update
                                </button>
                            </div>
                            <div
                                class="flex items-center justify-between py-2 px-3 bg-gray-400"
                            >
                                <h1 class="text-gray-800 font-bold">
                                    {{ this.item.created_at }}
                                </h1>
                                <button
                                    class="bg-gray-800 text-xs text-white px-2 py-1 font-semibold rounded uppercase hover:bg-gray-700"
                                    @click="expandImage"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
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
        item: {
            type: Object,
            default: {},
        },
    },
    data: function () {
        return {
            expand_image: false,
            title: null,
            alt: null,
            caption: null,
            description: null,
        };
    },
    methods: {
        expandImage() {
            if (this.expand_image) {
                this.expand_image = false;
            } else {
                this.expand_image = true;
            }
        },
        expandImageLoad() {
            this.expandImage();
            // Load the bind values for the edit fildes
            //console.log(file.title);
            this.title = this.item.title;
            this.alt = this.item.alt;
            this.caption = this.item.caption;
            this.description = this.item.description;
        },
        async updateDetails(id) {
            this.$emit("loading");
            await axios
                .post("/file/update/" + id, {
                    title: this.title,
                    alt: this.alt,
                    caption: this.caption,
                    description: this.description,
                })
                .then(function (response) {})
                .catch(function (error) {});
            this.$emit("loading");
            this.$emit("load_file");
            //this.loadFiles();
            //this.loading();
        },
    },
    created() {},
    computed: {},
    mounted() {},
};
</script>
<style></style>
