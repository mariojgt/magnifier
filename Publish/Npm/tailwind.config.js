const plugin = require('tailwindcss/plugin')

module.exports = {
    darkMode: 'class',
    purge: [
        "./vendor/Magnifier/src/views/**/*.php",
    ],
    theme: {
      extend: {},
    },
    variants: {
        extend: {
          textOpacity: ['dark']
        }
    },
    plugins: [],
  }
