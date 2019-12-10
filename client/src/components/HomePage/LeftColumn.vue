<template>
    <div class="container__left">
        <div class="flex justify-center items-center">
            <div class="flex flex-col">
                <h1 class="heading">Life is too short for unpleasent beverages.</h1>
                <p class="mt-16">
                    Multiple students have carelessly chosen between a collection of beverages to only be disapointed
                    with their experience.
                </p>
                <p>
                    At <strong>Refrigerator Supreme</strong> we seek to find the best beverage available at Cassiopeia
                    to never let anyone down again.
                </p>
                <Button class="w-1/4 mt-8" text="Start voting"/>
            </div>
        </div>
    </div>
</template>
<script>
import Button from '../Button'

export default {
  name: 'LeftColumn',
  components: { Button }
}
</script>
<style lang="scss" scoped>
  .container {
    &__left {
      background-color: #080808;
      color: #fff;
      @apply p-16 flex w-1/2 h-screen;

      p {
        @apply text-xl pb-12;
      }
    }
  }

  .heading {
    @apply text-6xl font-light;
  }
</style>
