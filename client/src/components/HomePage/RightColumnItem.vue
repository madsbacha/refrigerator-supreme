<template>
  <div :class="['container__right-' + level]">
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
  .container {
    &__right {
      @apply w-1/2 flex flex-col text-center text-white;

      &-upper,
      &-lower {
        @apply flex-auto;
      }

      &-upper {
        @apply orange;
      }

      &-lower {
        @apply yellow;
      }
    }
  }

  .subheading {
    @apply text-5xl font-light;
  }

  .uppercased {
    @apply uppercase;
  }

  .orange {
    background-color: #F17849;
  }

  .yellow {
    background-color: #FAB131;
  }
</style>
