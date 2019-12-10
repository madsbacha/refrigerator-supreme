<template>
    <div class="home bg-gray-200 h-screen">
        <div class="container mx-auto">
            <h1 class="text-3xl">Drinks</h1>
            <div class="flex flex-wrap content-between">
                <div v-for="item in items" v-bind:key="item.id" class="w-1/4">
                    <div class="shadow rounded m-3 bg-white">
                        <img :src="item.image" class="object-contain pt-4 px-4"/>
                        <p class="text-lg px-4">{{item.name}}</p>
                        <p class="px-4 pb-4 border-b border-grey-200">Rating: {{item.rating}}</p>
                        <div class="flex w-full justify-between">
                            <p class="py-2 px-3">Your rating</p>
                            <input @input="handleRating" v-bind:item-id="item.id" v-once :value="ratingOn(item.id)"
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
        items: []
    }),
    methods: {
        handleRating (e) {
            console.log(e)
            let rating = parseInt(e.target.value)
            if (typeof e.target.attributes['item-id'] === 'undefined') {
                return false
            }
            let itemId = parseInt(e.target.attributes['item-id'].value)
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
            for (let i = 0; i < this.items.length; i++) {
                if (this.items[i].id === id) {
                    return this.items[i].myRating ? this.items[i].myRating.rating : ''
                }
            }
            return ''
        }
    },
    apollo: {
        items: gql`query {
  items {
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
