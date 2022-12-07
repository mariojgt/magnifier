const plugin = require("tailwindcss/plugin");

module.exports = {
    mode: 'jit',
    darkMode: "class",
    purge: [
        "./vendor/Magnifier/src/views/**/*.php",
        "./resources/vendor/Magnifier/**/*.vue",
        "./node_modules/@mariojgt/wind-notify/packages/toasts/messages.js"
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
