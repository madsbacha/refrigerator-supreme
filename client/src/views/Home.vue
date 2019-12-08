<template>
    <div class="home bg-gray-200 h-screen">
        <div class="container mx-auto">
            <h1 class="text-3xl">Drinks</h1>
            <div class="flex flex-wrap content-between">
                <div v-for="drink in drinks" v-bind:key="drink.id" class="w-1/4">
                    <div class="shadow rounded m-3 bg-white">
                        <img :src="drink.image" class="object-contain pt-4 px-4"/>
                        <p class="text-lg px-4">{{drink.name}}</p>
                        <p class="px-4 pb-4 border-b border-grey-200">Rating: {{drink.rating}}</p>
                        <div class="flex w-full justify-between">
                            <p class="py-2 px-3">Your rating</p>
                            <input @input="handleRating" v-bind:drink-id="drink.id" v-once :value="ratingOn(drink.id)"
                                   type="number" placeholder="#" min="1" max="10"
                                   class="border-l border-grey-100 py-2 px-3 rounded-br w-16"/>
                        </div>
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
    methods: {
        handleRating (e) {
            console.log(e)
            let rating = parseInt(e.target.value)
            if (typeof e.target.attributes['drink-id'] === 'undefined') {
                return false
            }
            let itemId = parseInt(e.target.attributes['drink-id'].value)
            this.$apollo.mutate({
                mutation: gql`mutation ($rating: Float!, $itemId: ID!) {
        RateItem(itemId: $itemId, rating: $rating) {
            rating
        }
    }`,
                variables: {
                    rating: rating,
                    itemId: itemId
                },
                headers: {
                    Authorization: 'JWT ' + this.$token
                }
            }).then(console.log).catch(console.log)
        },
        ratingOn (id) {
            for (let i = 0; i < this.drinks.length; i++) {
                if (this.drinks[i].id === id) {
                    return this.drinks[i].myRating ? this.drinks[i].myRating.rating : ''
                }
            }
            return ''
        }
    },
    apollo: {
        drinks: gql`query {
  drinks {
    id,
    rating,
    name,
    image,
    myRating {
        rating
    }
  }
}`
    }
}
</script>
