const plugin = require("tailwindcss/plugin");

module.exports = {
    mode: 'jit',
    darkMode: "class",
    purge: [
        "./vendor/Magnifier/src/views/**/*.php",
        "./resources/vendor/Magnifier/**/*.vue",
    ],
    theme: {
        extend: {},
    },
    variants: {
        extend: {
            textOpacity: ["dark"],
        },
    },
    plugins: [require("daisyui")],
};
