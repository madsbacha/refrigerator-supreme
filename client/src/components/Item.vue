<template>
  <div class="item">
    <aside class="background">
      <article class="background__image" :style="{ backgroundImage: `linear-gradient(${backgroundGradient}), url(${ itemBackground })` }" />
    </aside>
    <section class="drink">
      <article class="container">
        <section class="inner">
          <article class="w-1/3 pr-10">
            <header class="text-white">
              <h1 class="text-6xl font-light leading-tight">Red Bull Energy Drink</h1> <!-- TODO: Add title to item type -->
              <p class="text-lg mt-12">
                Red Bull Energy Drink is appreciated worldwide by top athletes,
                busy professionals, college students and travelers on long journeys.
              </p> <!-- TODO: Add body paragraph to item type -->
            </header>
          </article>
          <article class="w-1/3">
            <figure class="flex justify-center">
              <img :src="`${itemImage}`" alt="test">
            </figure>
          </article>
          <article class="w-1/3 pl-10">
            <section class="flex">
              <article class="bg-white rounded py-6 px-4">
                <vue-feedback-reaction class="flex flex-col-reverse" v-model="feedback" />
                <p>{{ feedback }}</p>
              </article>
            </section>
          </article>
        </section>
      </article>
    </section>
    <section>

    </section>
  </div>
</template>

<script>
import ITEM from '../graphql/Item.gql'
import { VueFeedbackReaction } from 'vue-feedback-reaction'

export default {
  name: 'Item',
  components: {
    VueFeedbackReaction
  },
  data: () => ({
    item: null,
    feedback: ''
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
    },
    itemImage () {
      try {
        return require(`@/assets/item-${this.name}.png`)
      } catch (err) {
        return '' // TODO: replace with an image to be loaded if the image cannot be found
      }
    },
    backgroundGradient () {
      return 'to bottom, rgba(0, 0, 0, .0), rgba(0, 0, 0, .2), rgba(8, 8, 8, .6)'
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
.item {
  @apply relative w-full;

  .background {
    @apply relative;
    height: 866px;

    &__image {
      @apply absolute w-full top-0 bg-cover bg-center bg-no-repeat;
      height: 866px;
    }
  }

  .drink {
    @apply absolute top-0 w-full h-full;

    .container {
      @apply m-auto h-full;

      .inner {
        @apply flex items-center justify-center h-full;
      }
    }
  }
}
</style>
