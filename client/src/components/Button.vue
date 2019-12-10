<template>
  <button class="btn btn-white">
    {{ text }}
  </button>
</template>

<script>
export default {
  name: 'Button',
  props: {
    text: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
  .btn {
    min-width: 180px;
    @apply py-4 text-sm font-medium uppercase tracking-wider rounded;
  }
  .btn-white {
    @apply bg-white text-black;
  }
  .btn-white:hover {
    @apply bg-blue-700;
  }
</style>
