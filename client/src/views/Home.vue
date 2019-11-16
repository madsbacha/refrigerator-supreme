<template>
  <div class="home">
    <h1>Drinks</h1>
    <ul>
      <li v-for="drink in drinks" v-bind:key="drink.id" >{{drink.name}}</li>
    </ul>
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
