<template>
  <section class="background">
    <div :style="{ backgroundImage: `url(${ itemBackground })` }"></div>
  </section>
</template>

<script>
import gql from 'graphql-tag'

export default {
  name: 'Item.vue',
  data: () => ({
    item: []
  }),
  props: {
    name: String
  },
  computed: {
    itemBackground: function () {
      return require(`@/assets/bg-${this.item.slug}.png`)
    }
  },
  apollo: {
    item: {
      query: gql`query GetItem($name: String!) {
      item(slug: $name) {
        id,
        rating,
        name,
        image,
        slug
      }
    }`,
      variables () {
        return {
          name: this.name
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
