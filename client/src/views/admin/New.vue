<template>
    <div class="container flex m-5">
        <div class="p-5 w-1/2">
            <div class="flex justify-between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="name" class="label">Drink Name</label>
                    <input type="text" v-model="name" id="name" class="px-3 py-2 bg-gray-200 rounded shadow"
                           placeholder="Monster Energy">
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="price" class="label">Price</label>
                    <input type="text" id="price" class="input" placeholder="12,00 kr.">
                </div>
            </div>
            <div class="flex justify=between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="energy" class="label">Energy per 100 ml</label>
                    <input type="number" id="energy" class="input" placeholder="487 kJ">
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="mlSize" class="label">Milliliter</label>
                    <input type="number" id="mlSize" class="input" placeholder="250ml">
                </div>
            </div>
            <div class="flex mb-10">
                <div class="flex flex-col flex-grow">
                    <label for="imageUrl" class="label">Image URL</label>
                    <input type="text" class="input" id="imageUrl">
                </div>
            </div>
            <!--<div class="flex mb-10">
                <div class="flex flex-col">
                    <label for="image-file" class="label">Choose an image</label>
                    <input type="file" id="image-file" accept="image/*">
                </div>
            </div>-->
            <div class="flex">
                <button type="submit" v-on:click="addItem" class="btn-submit w-1/2 hover:bg-blue-700">Add Item</button>
            </div>
        </div>
        <div class="p-5 w-1/2">
            <div>
                <div class="list-item" v-for="item in items" :key="item.id">
                    <label>{{ item.name }}</label>
                    <button class="float-right" v-on:click="deleteItem(item.id)">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// import gql from 'graphql-tag'
import RETRIEVE_ITEMS from '../../graphql/Items.gql'
import CREATE_ITEM_MUTATION from '../../graphql/CreateItem.gql'
import DELETE_ITEM_MUTATION from '../../graphql/DeleteItem.gql'

export default {
    name: 'New',
    data: () => ({
        name: '',
        price: '',
        energy: '',
        mlSize: '',
        imageURL: '',
        items: []
    }),
    methods: {
        addItem () {
            this.$apollo.mutate({
                mutation: CREATE_ITEM_MUTATION,
                variables: {
                    name: this.name,
                    image: 'test.png'
                }
            }).then(data => this.alertUserOfAddedItem(data['data']['CreateItem']['name']))
        },
        alertUserOfAddedItem (name) {
            alert(`'${name}' was added successfully!`)
        },
        deleteItem (delId) {
            this.$apollo.mutate({
                mutation: DELETE_ITEM_MUTATION,
                variables: {
                    id: delId
                }
            }).then(response => console.log(response))
        }
    },
    apollo: {
        items: RETRIEVE_ITEMS
    }
}
</script>

<style scoped>

</style>
