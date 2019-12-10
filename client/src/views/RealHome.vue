<template>
  <div class="flex">
    <LeftColumn/>
    <div class="container__right">
      <RightColumnItem
        level="upper"
        leading-text="Already a supremer?"
        heading="Submit new vote"
      />
      <RightColumnItem
        level="lower"
        leading-text="Top rated beverage"
        heading="Monster Energy Juice"
      />
    </div>
  </div>
</template>

<script>
import LeftColumn from '../components/HomePage/LeftColumn'
import RightColumnItem from '../components/HomePage/RightColumnItem'

export default {
  name: 'RealHome.vue',
  components: {
    RightColumnItem,
    LeftColumn
  }
}
</script>

<style lang="scss" scoped>
  .container {
    &__right {
      @apply w-1/2 flex flex-col text-center text-white;
    }
  }
</style>
