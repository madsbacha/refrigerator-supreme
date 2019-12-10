<template>
  <div class="flex-auto" v-bind:style="{ 'background-color': bgColor }">
    <div class="flex flex-col justify-center items-center h-full">
      <div class="uppercase tracking-widest">{{ leadingText }}</div>
      <h2 class="subheading">{{ heading }}</h2>
    </div>
  </div>
</template>
<script>
export default {
  name: 'Upper',
  props: {
    bgColor: {
      type: String,
      required: true
    },
    level: {
      type: String,
      required: true
    },
    leadingText: String,
    heading: String
  }
}
</script>
<style lang="scss" scoped>
  .subheading {
    @apply text-5xl font-light;
  }

  .uppercased {
    @apply uppercase;
  }
</style>
