<template>
  <section class="background" :style="{ backgroundImage: `url(${ itemBackground })` }">
    <div></div>
  </section>
</template>

<script>
import ITEM from '../graphql/Item.gql'

export default {
  name: 'Item',
  data: () => ({
    item: null
  }),
  props: {
    name: {
      type: String,
      required: true
    }
  },
  computed: {
    itemBackground () {
      console.log('Run')
      try {
        return require(`@/assets/bg-${this.name}.png`)
      } catch (err) {
        return '' // TODO: replace with an image to be loaded if the image cannot be found
      }
    }
  },
  apollo: {
    item: {
      query: ITEM,
      variables () {
        return {
          slug: this.name
        }
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  .background {
    height: 500px;
  }
</style>
