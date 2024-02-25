<template>
  <div :class="class" v-html="iconSvg"></div>
</template>

<script>
import feather from 'feather-icons'

export default {
  name: 'app',
  props: ['name', 'class'],
  data: function () {
    return {
      message: 'Hello, World!',
      icon: 'flag'
    }
  },
  computed: {
    iconSvg: function () {
      return feather.icons[this.icon].toSvg();
    }
  },
  methods: {
    clicky: function () {
      this.message = 'clicked'
      this.icon = 'circle'
    }
  },
  mounted() {
      this.icon = this.name;
  },
}
</script>
