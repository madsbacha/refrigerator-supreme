<template>
    <form class="flex container m-5">
        <div class="p-5 w-1/2">
            <div class="flex justify-between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="name" class="label">Drink Name</label>
                    <input type="text" v-model="name" id="name" class="input" placeholder="Monster Energy" required>
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="price" class="label">Price</label>
                    <input type="number" v-model="price" id="price" class="input" placeholder="12,00 kr." required>
                </div>
            </div>
            <div class="flex justify=between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="energy" class="label">Energy per 100 ml</label>
                    <input type="number" v-model="energy" id="energy" class="input" placeholder="487 kJ" required>
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="mlSize" class="label">Milliliter</label>
                    <input type="number" v-model="size" id="mlSize" class="input" placeholder="250ml" required>
                </div>
            </div>
            <div class="flex mb-10">
                <div class="flex flex-col flex-grow">
                    <label for="imageUrl" class="label">Image URL</label>
                    <input type="text" v-model="imageURL" class="input" id="imageUrl" required>
                </div>
            </div>
            <!--<div class="flex mb-10">
                <div class="flex flex-col">
                    <label for="image-file" class="label">Choose an image</label>
                    <input type="file" id="image-file" accept="image/*">
                </div>
            </div>-->
            <div class="flex">
                <button type="submit" v-on:click="addItem" class="btn-submit w-1/2 hover:bg-blue-700">Add Item
                </button>
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
    </form>
</template>

<script>
import RETRIEVE_ITEMS from '../../graphql/Items.gql'
import CREATE_ITEM_MUTATION from '../../graphql/CreateItem.gql'
import DELETE_ITEM_MUTATION from '../../graphql/DeleteItem.gql'

export default {
    name: 'New',
    data: () => ({
        name: '',
        price: '',
        energy: '',
        size: '',
        imageURL: '',
        items: []
    }),
    methods: {
        addItem () {
            this.$apollo.mutate({
                mutation: CREATE_ITEM_MUTATION,
                variables: {
                    name: this.name,
                    image: this.imageURL,
                    price: this.price,
                    energy: this.energy,
                    size: this.size
                }
            }).then(res => {
                if (res['data']['CreateItem']) {
                    this.items.push(res['data']['CreateItem'])
                }
            })
        },
        deleteItem (delId) {
            this.$apollo.mutate({
                mutation: DELETE_ITEM_MUTATION,
                variables: {
                    id: delId
                }
            }).then(res => {
                if (res['data']['DeleteItem']['success']) {
                    const itemsIndex = this.items.findIndex(item => item.id === delId)
                    this.items.splice(itemsIndex, 1)
                }
            })
        }
    },
    apollo: {
        items: RETRIEVE_ITEMS
    }
}
</script>

<style scoped>

</style>
