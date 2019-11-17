<template>
  <div class="home bg-gray-200 h-screen">
    <div class="container mx-auto">
      <h1 class="text-3xl">Drinks</h1>
      <div class="flex flex-wrap content-between">
        <div v-for="drink in drinks" v-bind:key="drink.id" class="w-1/4">
          <div class="shadow rounded m-3 p-4 bg-white">
            <img :src="drink.image" class="object-contain" />
            <p class="text-lg">{{drink.name}}</p>
            <p>Rating: {{drink.rating}}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import gql from 'graphql-tag'
export default {
  name: 'home',
  data: () => ({
    drinks: []
  }),
  apollo: {
    drinks: gql`query {
      drinks {
        id,
        rating,
        name,
        image,
      }
    }`
  }
}
</script>
